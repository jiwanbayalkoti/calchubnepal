{{-- Phase 3 QR input field groups --}}
<div class="qr-field-group d-none" data-types="telegram">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="qr_tg_user">{{ __('qr.field_tg_username') }}</label>
            <input type="text" id="qr_tg_user" name="input[username]" class="form-control" placeholder="@username" disabled>
            <div class="invalid-feedback" data-error-for="input.username"></div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="qr_tg_phone">{{ __('qr.field_phone') }}</label>
            <input type="tel" id="qr_tg_phone" name="input[phone]" class="form-control" placeholder="97798XXXXXXXX" disabled>
            <div class="invalid-feedback" data-error-for="input.phone"></div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="qr_tg_msg">{{ __('qr.field_message') }}</label>
            <input type="text" id="qr_tg_msg" name="input[message]" class="form-control" disabled>
            <div class="invalid-feedback" data-error-for="input.message"></div>
        </div>
    </div>
</div>

<div class="qr-field-group d-none" data-types="viber">
    <div class="row g-3">
        <div class="col-md-5">
            <label class="form-label" for="qr_viber_phone">{{ __('qr.field_viber_phone') }}</label>
            <input type="tel" id="qr_viber_phone" name="input[phone]" class="form-control" placeholder="97798XXXXXXXX" disabled>
            <div class="invalid-feedback" data-error-for="input.phone"></div>
        </div>
        <div class="col-md-7">
            <label class="form-label" for="qr_viber_msg">{{ __('qr.field_message') }}</label>
            <input type="text" id="qr_viber_msg" name="input[message]" class="form-control" disabled>
            <div class="invalid-feedback" data-error-for="input.message"></div>
        </div>
    </div>
</div>

<div class="qr-field-group d-none" data-types="messenger">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="qr_ms_user">{{ __('qr.field_ms_username') }}</label>
            <input type="text" id="qr_ms_user" name="input[username]" class="form-control" disabled>
            <div class="invalid-feedback" data-error-for="input.username"></div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="qr_ms_page">{{ __('qr.field_page_id') }}</label>
            <input type="text" id="qr_ms_page" name="input[page_id]" class="form-control" disabled>
            <div class="invalid-feedback" data-error-for="input.page_id"></div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="qr_ms_url">{{ __('qr.field_mme_url') }}</label>
            <input type="text" id="qr_ms_url" name="input[url]" class="form-control" placeholder="https://m.me/..." disabled>
            <div class="invalid-feedback" data-error-for="input.url"></div>
        </div>
    </div>
</div>

<div class="qr-field-group d-none" data-types="mecard">
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="qr_mc_name">{{ __('qr.field_name') }}</label><input id="qr_mc_name" name="input[name]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.name"></div></div>
        <div class="col-md-6"><label class="form-label" for="qr_mc_phone">{{ __('qr.field_phone') }}</label><input id="qr_mc_phone" name="input[phone]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.phone"></div></div>
        <div class="col-md-6"><label class="form-label" for="qr_mc_email">{{ __('qr.field_email') }}</label><input type="email" id="qr_mc_email" name="input[email]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.email"></div></div>
        <div class="col-md-6"><label class="form-label" for="qr_mc_url">{{ __('qr.field_website') }}</label><input id="qr_mc_url" name="input[url]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.url"></div></div>
        <div class="col-md-6"><label class="form-label" for="qr_mc_addr">{{ __('qr.field_address') }}</label><input id="qr_mc_addr" name="input[address]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.address"></div></div>
        <div class="col-md-6"><label class="form-label" for="qr_mc_note">{{ __('qr.field_note') }}</label><input id="qr_mc_note" name="input[note]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.note"></div></div>
    </div>
</div>

<div class="qr-field-group d-none" data-types="app">
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label" for="qr_app_store">{{ __('qr.field_app_store') }}</label>
            <select id="qr_app_store" name="input[store]" class="form-select" disabled>
                <option value="auto">{{ __('qr.store_auto') }}</option>
                <option value="ios">App Store</option>
                <option value="android">Play Store</option>
            </select>
        </div>
        <div class="col-md-3"><label class="form-label" for="qr_app_ios">{{ __('qr.field_ios_url') }}</label><input id="qr_app_ios" name="input[ios_url]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.ios_url"></div></div>
        <div class="col-md-3"><label class="form-label" for="qr_app_and">{{ __('qr.field_android_url') }}</label><input id="qr_app_and" name="input[android_url]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.android_url"></div></div>
        <div class="col-md-3"><label class="form-label" for="qr_app_url">{{ __('qr.field_app_url') }}</label><input id="qr_app_url" name="input[url]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.url"></div></div>
    </div>
</div>

<div class="qr-field-group d-none" data-types="pdf">
    <label class="form-label" for="qr_pdf_url">{{ __('qr.field_pdf_url') }}</label>
    <input type="text" id="qr_pdf_url" name="input[url]" class="form-control" placeholder="https://example.com/file.pdf" disabled>
    <div class="invalid-feedback" data-error-for="input.url"></div>
</div>

<div class="qr-field-group d-none" data-types="image">
    <p class="small text-muted-custom mb-2">{{ __('qr.image_help') }}</p>
    <label class="form-label" for="qr_img_url">{{ __('qr.field_image_url') }}</label>
    <input type="text" id="qr_img_url" name="input[url]" class="form-control" placeholder="https://example.com/photo.jpg" disabled>
    <div class="invalid-feedback" data-error-for="input.url"></div>
</div>

<div class="qr-field-group d-none" data-types="crypto">
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label" for="qr_crypto_coin">{{ __('qr.field_coin') }}</label>
            <select id="qr_crypto_coin" name="input[coin]" class="form-select" disabled>
                <option value="bitcoin">Bitcoin</option>
                <option value="ethereum">Ethereum</option>
                <option value="usdt">USDT</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="col-md-5"><label class="form-label" for="qr_crypto_addr">{{ __('qr.field_wallet') }}</label><input id="qr_crypto_addr" name="input[address]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.address"></div></div>
        <div class="col-md-2"><label class="form-label" for="qr_crypto_amt">{{ __('qr.field_amount') }}</label><input id="qr_crypto_amt" name="input[amount]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.amount"></div></div>
        <div class="col-md-2"><label class="form-label" for="qr_crypto_label">{{ __('qr.field_label') }}</label><input id="qr_crypto_label" name="input[label]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.label"></div></div>
    </div>
</div>

<div class="qr-field-group d-none" data-types="esewa">
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label" for="qr_esewa_id">{{ __('qr.field_esewa_id') }}</label><input id="qr_esewa_id" name="input[esewa_id]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.esewa_id"></div></div>
        <div class="col-md-4"><label class="form-label" for="qr_esewa_name">{{ __('qr.field_account_name') }}</label><input id="qr_esewa_name" name="input[name]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.name"></div></div>
        <div class="col-md-2"><label class="form-label" for="qr_esewa_amt">{{ __('qr.field_amount') }}</label><input id="qr_esewa_amt" name="input[amount]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.amount"></div></div>
        <div class="col-md-2"><label class="form-label" for="qr_esewa_url">{{ __('qr.field_pay_url') }}</label><input id="qr_esewa_url" name="input[url]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.url"></div></div>
        <div class="col-12"><label class="form-label" for="qr_esewa_purpose">{{ __('qr.field_purpose') }}</label><input id="qr_esewa_purpose" name="input[purpose]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.purpose"></div></div>
    </div>
</div>

<div class="qr-field-group d-none" data-types="khalti">
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label" for="qr_khalti_id">{{ __('qr.field_khalti_id') }}</label><input id="qr_khalti_id" name="input[khalti_id]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.khalti_id"></div></div>
        <div class="col-md-4"><label class="form-label" for="qr_khalti_name">{{ __('qr.field_account_name') }}</label><input id="qr_khalti_name" name="input[name]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.name"></div></div>
        <div class="col-md-2"><label class="form-label" for="qr_khalti_amt">{{ __('qr.field_amount') }}</label><input id="qr_khalti_amt" name="input[amount]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.amount"></div></div>
        <div class="col-md-2"><label class="form-label" for="qr_khalti_url">{{ __('qr.field_pay_url') }}</label><input id="qr_khalti_url" name="input[url]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.url"></div></div>
        <div class="col-12"><label class="form-label" for="qr_khalti_purpose">{{ __('qr.field_purpose') }}</label><input id="qr_khalti_purpose" name="input[purpose]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.purpose"></div></div>
    </div>
</div>

<div class="qr-field-group d-none" data-types="upi">
    <p class="small text-muted-custom mb-2">{{ __('qr.upi_help') }}</p>
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label" for="qr_upi_pa">{{ __('qr.field_upi_id') }}</label><input id="qr_upi_pa" name="input[pa]" class="form-control" placeholder="name@upi" disabled><div class="invalid-feedback" data-error-for="input.pa"></div></div>
        <div class="col-md-4"><label class="form-label" for="qr_upi_pn">{{ __('qr.field_payee_name') }}</label><input id="qr_upi_pn" name="input[pn]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.pn"></div></div>
        <div class="col-md-2"><label class="form-label" for="qr_upi_am">{{ __('qr.field_amount') }}</label><input id="qr_upi_am" name="input[am]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.am"></div></div>
        <div class="col-md-2"><label class="form-label" for="qr_upi_tn">{{ __('qr.field_note') }}</label><input id="qr_upi_tn" name="input[tn]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.tn"></div></div>
    </div>
</div>

<div class="qr-field-group d-none" data-types="nepal_qr">
    <p class="small text-muted-custom mb-2">{{ __('qr.nepal_qr_help') }}</p>
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label" for="qr_nqr_id">{{ __('qr.field_merchant_id') }}</label><input id="qr_nqr_id" name="input[merchant_id]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.merchant_id"></div></div>
        <div class="col-md-4"><label class="form-label" for="qr_nqr_name">{{ __('qr.field_merchant_name') }}</label><input id="qr_nqr_name" name="input[merchant_name]" class="form-control" maxlength="25" disabled><div class="invalid-feedback" data-error-for="input.merchant_name"></div></div>
        <div class="col-md-2"><label class="form-label" for="qr_nqr_city">{{ __('qr.field_city') }}</label><input id="qr_nqr_city" name="input[city]" class="form-control" value="Kathmandu" disabled><div class="invalid-feedback" data-error-for="input.city"></div></div>
        <div class="col-md-2"><label class="form-label" for="qr_nqr_amt">{{ __('qr.field_amount') }}</label><input id="qr_nqr_amt" name="input[amount]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.amount"></div></div>
        <div class="col-md-6"><label class="form-label" for="qr_nqr_guid">{{ __('qr.field_guid') }}</label><input id="qr_nqr_guid" name="input[guid]" class="form-control" value="np.com.fonepay" disabled><div class="invalid-feedback" data-error-for="input.guid"></div></div>
        <div class="col-md-6"><label class="form-label" for="qr_nqr_mcc">{{ __('qr.field_mcc') }}</label><input id="qr_nqr_mcc" name="input[mcc]" class="form-control" value="0000" maxlength="4" disabled><div class="invalid-feedback" data-error-for="input.mcc"></div></div>
    </div>
</div>

<div class="qr-field-group d-none" data-types="calendar">
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="qr_cal_title">{{ __('qr.field_event_title') }}</label><input id="qr_cal_title" name="input[title]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.title"></div></div>
        <div class="col-md-3"><label class="form-label" for="qr_cal_start">{{ __('qr.field_event_start') }}</label><input type="datetime-local" id="qr_cal_start" name="input[start]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.start"></div></div>
        <div class="col-md-3"><label class="form-label" for="qr_cal_end">{{ __('qr.field_event_end') }}</label><input type="datetime-local" id="qr_cal_end" name="input[end]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.end"></div></div>
        <div class="col-md-6"><label class="form-label" for="qr_cal_loc">{{ __('qr.field_event_location') }}</label><input id="qr_cal_loc" name="input[location]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.location"></div></div>
        <div class="col-md-6"><label class="form-label" for="qr_cal_det">{{ __('qr.field_event_description') }}</label><input id="qr_cal_det" name="input[details]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.details"></div></div>
    </div>
</div>

<div class="qr-field-group d-none" data-types="meeting">
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label" for="qr_meet_plat">{{ __('qr.field_meeting_platform') }}</label>
            <select id="qr_meet_plat" name="input[platform]" class="form-select" disabled>
                <option value="zoom">Zoom</option>
                <option value="meet">Google Meet</option>
                <option value="teams">Microsoft Teams</option>
            </select>
        </div>
        <div class="col-md-5"><label class="form-label" for="qr_meet_url">{{ __('qr.field_meeting_url') }}</label><input id="qr_meet_url" name="input[url]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.url"></div></div>
        <div class="col-md-2"><label class="form-label" for="qr_meet_id">{{ __('qr.field_meeting_id') }}</label><input id="qr_meet_id" name="input[meeting_id]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.meeting_id"></div></div>
        <div class="col-md-2"><label class="form-label" for="qr_meet_pwd">{{ __('qr.field_password') }}</label><input id="qr_meet_pwd" name="input[password]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.password"></div></div>
    </div>
</div>

<div class="qr-field-group d-none" data-types="music">
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label" for="qr_music_plat">{{ __('qr.field_music_platform') }}</label>
            <select id="qr_music_plat" name="input[platform]" class="form-select" disabled>
                <option value="spotify">Spotify</option>
                <option value="youtube">YouTube Music</option>
                <option value="apple">Apple Music</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="col-md-9"><label class="form-label" for="qr_music_url">{{ __('qr.field_music_url') }}</label><input id="qr_music_url" name="input[url]" class="form-control" placeholder="https://open.spotify.com/..." disabled><div class="invalid-feedback" data-error-for="input.url"></div></div>
    </div>
</div>

<div class="qr-field-group d-none" data-types="review">
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="qr_rev_url">{{ __('qr.field_review_url') }}</label><input id="qr_rev_url" name="input[url]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.url"></div></div>
        <div class="col-md-6"><label class="form-label" for="qr_rev_pid">{{ __('qr.field_place_id') }}</label><input id="qr_rev_pid" name="input[place_id]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.place_id"></div></div>
    </div>
</div>

<div class="qr-field-group d-none" data-types="coupon">
    <div class="row g-3">
        <div class="col-md-3"><label class="form-label" for="qr_cp_code">{{ __('qr.field_promo_code') }}</label><input id="qr_cp_code" name="input[code]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.code"></div></div>
        <div class="col-md-3"><label class="form-label" for="qr_cp_title">{{ __('qr.field_promo_title') }}</label><input id="qr_cp_title" name="input[title]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.title"></div></div>
        <div class="col-md-3"><label class="form-label" for="qr_cp_url">{{ __('qr.field_redeem_url') }}</label><input id="qr_cp_url" name="input[url]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.url"></div></div>
        <div class="col-md-3"><label class="form-label" for="qr_cp_exp">{{ __('qr.field_expires') }}</label><input id="qr_cp_exp" name="input[expires]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.expires"></div></div>
        <div class="col-12"><label class="form-label" for="qr_cp_terms">{{ __('qr.field_terms') }}</label><input id="qr_cp_terms" name="input[terms]" class="form-control" disabled><div class="invalid-feedback" data-error-for="input.terms"></div></div>
    </div>
</div>

<div class="qr-field-group d-none" data-types="multi_url">
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label" for="qr_mu_title">{{ __('qr.field_list_title') }}</label><input id="qr_mu_title" name="input[title]" class="form-control" value="My links" disabled><div class="invalid-feedback" data-error-for="input.title"></div></div>
        <div class="col-md-8"><label class="form-label" for="qr_mu_urls">{{ __('qr.field_multi_urls') }}</label><textarea id="qr_mu_urls" name="input[urls]" class="form-control" rows="4" placeholder="https://example.com&#10;https://instagram.com/..." disabled></textarea><div class="invalid-feedback" data-error-for="input.urls"></div></div>
    </div>
</div>
