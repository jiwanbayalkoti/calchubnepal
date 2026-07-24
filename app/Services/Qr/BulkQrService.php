<?php

namespace App\Services\Qr;

use App\Models\QrBulkJob;
use App\Models\QrBrandTemplate;
use App\Models\QrCampaign;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use ZipArchive;

class BulkQrService
{
    public function __construct(
        protected DynamicQrService $dynamic,
        protected QrEntitlementService $entitlements,
        protected BrandTemplateService $templates,
    ) {
    }

    public function start(User $user, UploadedFile $file, array $options = []): QrBulkJob
    {
        if (! $this->entitlements->canUseBulk($user)) {
            throw new InvalidArgumentException('Bulk QR is not available on your plan.');
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'csv');
        if (! in_array($ext, ['csv', 'txt', 'xlsx', 'xls'], true)) {
            throw new InvalidArgumentException('Upload a CSV or Excel (.xlsx) file.');
        }

        // For xlsx without PhpSpreadsheet: ask for CSV. Accept xlsx only if we can parse as zip+xml lightly — keep CSV primary.
        if (in_array($ext, ['xlsx', 'xls'], true)) {
            throw new InvalidArgumentException('Please export your Excel sheet as CSV and upload the CSV file.');
        }

        $path = $file->storeAs('qr-bulk/inputs', Str::uuid().'.csv', 'local');
        $rows = $this->parseCsv(Storage::disk('local')->path($path));
        $max = $this->entitlements->maxBulkRows($user);
        if (count($rows) > $max) {
            throw new InvalidArgumentException("Your plan allows up to {$max} rows per bulk job.");
        }
        if ($rows === []) {
            throw new InvalidArgumentException('No valid rows found. CSV needs title,destination_url columns.');
        }

        $job = QrBulkJob::query()->create([
            'user_id' => $user->id,
            'workspace_id' => $options['workspace_id'] ?? null,
            'campaign_id' => $options['campaign_id'] ?? null,
            'brand_template_id' => $options['brand_template_id'] ?? null,
            'status' => 'processing',
            'total_rows' => count($rows),
            'input_path' => $path,
            'error_log' => [],
        ]);

        return $this->process($job, $rows, $user);
    }

    /**
     * @param  list<array{title?: string, destination_url: string}>  $rows
     */
    public function process(QrBulkJob $job, array $rows, User $user): QrBulkJob
    {
        $template = $job->brand_template_id
            ? QrBrandTemplate::query()->whereKey($job->brand_template_id)->where('user_id', $user->id)->first()
            : null;
        $campaign = $job->campaign_id
            ? QrCampaign::query()->whereKey($job->campaign_id)->where('user_id', $user->id)->first()
            : null;

        $tmpDir = storage_path('app/qr-bulk/tmp/'.$job->uuid);
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $errors = [];
        $processed = 0;
        $failed = 0;
        $manifest = [['title', 'short_url', 'destination_url', 'uuid']];

        foreach ($rows as $index => $row) {
            try {
                $destination = (string) ($row['destination_url'] ?? '');
                if ($campaign) {
                    $destination = $campaign->appendUtm($destination);
                }
                $payload = $this->templates->applyToPayload([
                    'destination_url' => $destination,
                    'title' => (string) ($row['title'] ?? ('Bulk '.($index + 1))),
                    'workspace_id' => $job->workspace_id,
                    'campaign_id' => $job->campaign_id,
                    'brand_template_id' => $job->brand_template_id,
                ], $template);

                $created = $this->dynamic->create($payload, null, $user->id);
                $qr = $created['qr'];
                if ($job->workspace_id || $job->campaign_id || $job->brand_template_id) {
                    $qr->update([
                        'workspace_id' => $job->workspace_id,
                        'campaign_id' => $job->campaign_id,
                        'brand_template_id' => $job->brand_template_id,
                    ]);
                }

                $pngName = 'qr-'.($index + 1).'-'.$qr->short_code.'.png';
                file_put_contents($tmpDir.'/'.$pngName, $created['result']->binary);
                $manifest[] = [$qr->title, $created['short_url'], $qr->destination_url, $qr->uuid];
                $processed++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = ['row' => $index + 1, 'message' => $e->getMessage()];
            }
        }

        $csvPath = $tmpDir.'/manifest.csv';
        $fh = fopen($csvPath, 'w');
        foreach ($manifest as $line) {
            fputcsv($fh, $line);
        }
        fclose($fh);

        $zipRelative = 'qr-bulk/zips/'.$job->uuid.'.zip';
        $zipFull = Storage::disk('public')->path($zipRelative);
        if (! is_dir(dirname($zipFull))) {
            mkdir(dirname($zipFull), 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipFull, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new InvalidArgumentException('Unable to create ZIP archive.');
        }
        foreach (glob($tmpDir.'/*') as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();

        // cleanup tmp
        foreach (glob($tmpDir.'/*') as $file) {
            @unlink($file);
        }
        @rmdir($tmpDir);

        $job->update([
            'status' => 'completed',
            'processed_rows' => $processed,
            'failed_rows' => $failed,
            'output_zip_path' => $zipRelative,
            'error_log' => $errors,
        ]);

        return $job->refresh();
    }

    /**
     * @return list<array{title?: string, destination_url: string}>
     */
    protected function parseCsv(string $path): array
    {
        $fh = fopen($path, 'r');
        if ($fh === false) {
            return [];
        }
        $header = null;
        $rows = [];
        while (($data = fgetcsv($fh)) !== false) {
            if ($data === [null] || $data === false) {
                continue;
            }
            if ($header === null) {
                $header = array_map(static fn ($h) => Str::snake(trim(strtolower((string) $h))), $data);
                continue;
            }
            $row = [];
            foreach ($header as $i => $key) {
                $row[$key] = trim((string) ($data[$i] ?? ''));
            }
            // aliases
            $destination = $row['destination_url'] ?? $row['url'] ?? $row['destination'] ?? '';
            if ($destination === '') {
                continue;
            }
            $rows[] = [
                'title' => $row['title'] ?? $row['name'] ?? '',
                'destination_url' => $destination,
            ];
        }
        fclose($fh);

        return $rows;
    }
}
