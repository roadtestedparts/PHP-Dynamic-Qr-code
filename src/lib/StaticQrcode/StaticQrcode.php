<?php
require_once 'config/config.php';
require_once BASE_PATH . '/lib/ICS/ICS.php';
require_once BASE_PATH . '/lib/vCard/vCard.php';

if (QRCODE_GENERATOR === "external-api.qrserver.com") {
    require_once BASE_PATH . '/lib/Qrcode/Qrcode.php';
}

if (QRCODE_GENERATOR === "internal-chillerlan.qrcode") {
    require_once BASE_PATH . '/lib/Qrcode/Qrcode-intchil.php';
}

class StaticQrcode {
    private $sData;         // Data for the qr code
    private $sContent;      // Content to be stored in the database
    private Qrcode $qrcode_instance;
    /**
     *
     */
    public function __construct() {
        $this->qrcode_instance = new Qrcode("static");
    }

    /**
     *
     */
    public function __destruct()
    {
    }

    private function normalizeInput(?string $value): string
    {
        return trim(normalize_html_entities($value ?? ''));
    }
    
    /**
     * Set friendly columns\' names to order tables\' entries
     */
    public function setOrderingValues()
    {
        $ordering = [
            'id' => 'ID',
            'id_owner' => 'Owner',
            'filename' => 'File Name',
            'type' => 'Type',
            'content' => 'Content',
            'qrcode' => 'Qr Code',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at'
        ];

        return $ordering;
    }
    
    /**
     * create a qr code of type "text"
     * @string text -> required
     */
    public function textQrcode($text)
    {
        $text = $this->normalizeInput($text);

        if($text !== ''){
            $this->sData = $text;
            $this->sContent = '<strong>Text:</strong> '.$text;
            $this->addQrcode("text");
        }
        else
            $this->requiredFieldsError();
    }
    
    /**
     * create a qr code of type "email"
     * @string email -> required
     * @string subject
     * @string message -> required
     */
    public function emailQrcode($email, $subject, $message)
    {
        $email = $this->normalizeInput($email);
        $subject = $this->normalizeInput($subject);
        $message = $this->normalizeInput($message);

        if($email !== '' && $message !== ''){
            $this->sData = 'MATMSG:TO:'.$email.';SUB:'.$subject.';BODY:'.$message.';';
            $this->sContent = '<strong>Email:</strong> '.$email.'<br>'.'<strong>Subject:</strong> '.$subject.'<br>'.'<strong>Message:</strong> '.$message;

            $this->addQrcode("email");
        }
        else
            $this->requiredFieldsError();
    }
    
    /**
     * create a qr code of type "phone"
     * @int country code -> required
     * @string phone number -> required
     */
    public function phoneQrcode($country_code, $phone_number)
    {
        $country_code = $this->normalizeInput($country_code);
        $phone_number = $this->normalizeInput($phone_number);

        if($phone_number !== ''){
            $this->sData = 'TEL:'.$country_code.$phone_number;
            $this->sContent = '<strong>Phone number:</strong> '.$country_code.$phone_number;

            $this->addQrcode("phone");
        }
        else
            $this->requiredFieldsError();
    }
    
    /**
     * create a qr code of type "sms"
     * @int country code -> required
     * @string phone number -> required
     * @string message -> required
     */
    public function smsQrcode($country_code, $phone_number, $message)
    {
        $country_code = $this->normalizeInput($country_code);
        $phone_number = $this->normalizeInput($phone_number);
        $message = $this->normalizeInput($message);

        if($phone_number !== '' && $message !== ''){
            $this->sData = 'SMSTO:'.$country_code.$phone_number.':'.$message;
            $this->sContent = '<strong>Phone number:</strong> '.$country_code.$phone_number.'<br>'.'<strong>Message:</strong> '.$message;

            $this->addQrcode("sms");
        }
        else
            $this->requiredFieldsError();
    }
    
    /**
     * create a qr code of type "whatsapp"
     * @int country code -> required
     * @string phone number -> required
     * @string message
     */
    public function whatsappQrcode($country_code, $phone_number, $message)
    {
        $country_code = $this->normalizeInput($country_code);
        $phone_number = $this->normalizeInput($phone_number);
        $message = $this->normalizeInput($message);

        if($phone_number !== ''){
            $this->sData = 'https://wa.me/'.$country_code.$phone_number.'?text='.$message;
            $this->sContent = '<strong>Phone number:</strong> '.$country_code.$phone_number.'<br>'.'<strong>Message:</strong> '.$message;

            $this->addQrcode("whatsapp");
        }
        else
            $this->requiredFieldsError();
    }
    
    /**
     * create a qr code of type "skype"
     * @string skype username -> required
     */
    public function skypeQrcode($skype_username)
    {
        $skype_username = $this->normalizeInput($skype_username);

        if($skype_username !== ''){
            $this->sData = 'skype:'.$skype_username.'?call';
            $this->sContent = '<strong>Skype username:</strong> '.$skype_username;

            $this->addQrcode("skype");
        }
        else
            $this->requiredFieldsError();
    }
    
    /**
     * create a qr code of type "location"
     * @int latitude -> required
     * @int longitude -> required
     */
    public function locationQrcode($latitude, $longitude)
    {
        $latitude = $this->normalizeInput($latitude);
        $longitude = $this->normalizeInput($longitude);

        if($latitude !== '' && $longitude !== ''){
            $this->sData = 'GEO:'.$latitude.','.$longitude.';';
            $this->sContent = '<strong>Latitude:</strong> '.$latitude.'<br>'.'<strong>Longitude:</strong> '.$longitude;

            $this->addQrcode("location");
        }
        else
            $this->requiredFieldsError();
    }
    
    /**
     * create a qr code of type "vcard"
     * 
     */
    public function vcardQrcode($fullname, $nickname, $email, $website, $phone, $home_phone, $work_phone, $company, $role, $categories, $note, $photo, $address, $city, $postcode, $state)
    {
        $fullname = $this->normalizeInput($fullname);
        $nickname = $this->normalizeInput($nickname);
        $email = $this->normalizeInput($email);
        $website = $this->normalizeInput($website);
        $phone = $this->normalizeInput($phone);
        $home_phone = $this->normalizeInput($home_phone);
        $work_phone = $this->normalizeInput($work_phone);
        $company = $this->normalizeInput($company);
        $role = $this->normalizeInput($role);
        $categories = $this->normalizeInput($categories);
        $note = $this->normalizeInput($note);
        $photo = $this->normalizeInput($photo);
        $address = $this->normalizeInput($address);
        $city = $this->normalizeInput($city);
        $postcode = $this->normalizeInput($postcode);
        $state = $this->normalizeInput($state);

        if($fullname !== '' && $phone !== ''){

            $vcard = new vCard;
            $vcard->name($fullname);
            $vcard->nickName($nickname);
            $vcard->email($email);
            $vcard->url($website);
            $vcard->cellPhone($phone);
            $vcard->homePhone($home_phone);
            $vcard->workPhone($work_phone);
            $vcard->organization($company);
            $vcard->role($role);
            $vcard->categories($categories);
            $vcard->note($note);
            $vcard->photo($photo);
            $vcard->address($address, $city, $postcode, $state);
            $vcard->create();

            $this->sData = $vcard->get();
            $this->sContent = '<div class="row"><div class="col-sm-4">';
            
                $this->sContent .= '<strong>Full name:</strong> '.$fullname.'<br>'.'<strong>Nickname:</strong> '.$nickname.'<br>'.'<strong>Email:</strong> '.$email.'<br>'.'<strong>Website:</strong> '.$website.'</div>';
            
            $this->sContent .= '<div class="col-sm-4">';
            
                $this->sContent .= '<strong>Company:</strong> '.$company.'<br>'.'<strong>Role:</strong> '.$role.'<br>'.'<strong>Categories:</strong> '.$categories.'<br>'.'<strong>Note:</strong> '.$note.'</div>';
                
            $this->sContent .= '<div class="col-sm-4">';
            
                $this->sContent .= '<strong>Phone:</strong> '.$phone.'<br>'.'<strong>Home Phone:</strong> '.$home_phone.'<br>'.'<strong>Work phone:</strong> '.$work_phone.'<br>'.'<strong>Address:</strong> '.$address.'&nbsp;'.$city.'&nbsp;'.$postcode.'&nbsp;'.$state.'</div>';
            
            $this->sContent .= '</div>';

            $this->addQrcode("vcard");
        }
        else
            $this->requiredFieldsError();
    }
    
    /**
     * create a qr code of type "event"
     * @string description -> required
     * @string start event -> required
     * @string end event -> required
     * @string location
     * @string summary
     * @string url
     */
    public function eventQrcode($title, $start, $end, $timezone, $location, $description, $url)
    {
        $title = $this->normalizeInput($title);
        $start = $this->normalizeInput($start);
        $end = $this->normalizeInput($end);
        $timezone = $this->normalizeInput($timezone);
        $location = $this->normalizeInput($location);
        $description = $this->normalizeInput($description);
        $url = $this->normalizeInput($url);

        if($title !== '' && $start !== '' && $end !== ''){
            header('Content-Type: text/calendar; charset=utf-8');
            header('Content-Disposition: attachment; filename=invite.ics');

            $timezoneFrom = $timezone;

            if(empty($timezoneFrom)){
                $timezoneFrom = 'Europe/Berlin';
            }

            $tmpStart = new DateTime($start, new DateTimeZone($timezoneFrom));
            $tmpEnd = new DateTime($end, new DateTimeZone($timezoneFrom));
            $tmpStart->setTimezone(new DateTimeZone("UTC"));
            $tmpEnd->setTimezone(new DateTimeZone("UTC"));

            $ics = new ICS(array(
                'location' => $location,
                'description' => $description,
                'dtstart' => $tmpStart->format("Y-m-d H:i:s"),
                'dtend' => $tmpEnd->format("Y-m-d H:i:s"),
                'summary' => $title,
                'url' => $url
            ));
            
            $this->sData = $ics->to_string();
            $this->sContent = '<div class="row"><div class="col-sm-4">';
            $this->sContent .= '<strong>Title:</strong> '.$title.'<br>'.'<strong>Start event:</strong> '.$start.'<br>'.'<strong>End event:</strong> '.$end.'<br>'.'<strong>Time zone:</strong> '.$timezone.'<br></div>';
            $this->sContent .= '<div class="col-sm-4">';
            $this->sContent .= '<strong>Location:</strong> '.$location.'<br>'.'<strong>Description:</strong> '.$description.'<br>'.'<strong>URL:</strong> '.$url.'</div>';
            $this->sContent .= '</div>';

            $this->addQrcode("event");
        }
        else
            $this->requiredFieldsError();
    }
    
    /**
     * create a qr code of type "bookmark"
     * @string title
     * @string url -> required
     */
    public function bookmarkQrcode($url, $title)
    {
        $url = $this->normalizeInput($url);
        $title = $this->normalizeInput($title);

        if($url !== ''){
            $this->sData = 'MEBKM:TITLE:'.$title.';URL:'.$url.';';
            $this->sContent = '<strong>Title:</strong> '.$title.'<br>'.'<strong>Url:</strong> '.$url;

            $this->addQrcode("bookmark");
        }
        else
            $this->requiredFieldsError();
    }
    
    /**
     * create a qr code of type "wifi"
     * @string encryption -> required
     * @string ssid -> required
     * @string password
     */
    public function wifiQrcode($encryption, $ssid, $password)
    {
        $encryption = $this->normalizeInput($encryption);
        $ssid = $this->normalizeInput($ssid);
        $password = $this->normalizeInput($password);

        if($ssid !== ''){
            $this->sData = 'WIFI:T:'.$encryption.';S:'.$ssid.';P:'.$password.';';
            $this->sContent = '<strong>Encryption:</strong> '.$encryption.'<br>'.'<strong>SSID:</strong> '.$ssid.'<br>'.'<strong>Password:</strong> '.$password;

            $this->addQrcode("wifi");
        }
        else
            $this->requiredFieldsError();
    }
    
    /**
     * create a qr code of type "paypal"
     * @string payment type -> required
     * @string email -> required
     * @string item_name -> required
     * @int item_id
     * @int amount -> required
     * @string currency -> required
     * @int shipping
     * @int tax_rate
     */
    public function paypalQrcode($payment_type, $email, $item_name, $item_id, $amount, $currency, $shipping, $tax_rate)
    {
        $payment_type = $this->normalizeInput($payment_type);
        $email = $this->normalizeInput($email);
        $item_name = $this->normalizeInput($item_name);
        $item_id = $this->normalizeInput($item_id);
        $amount = $this->normalizeInput($amount);
        $currency = $this->normalizeInput($currency);
        $shipping = $this->normalizeInput($shipping);
        $tax_rate = $this->normalizeInput($tax_rate);

        if($email !== '' && $item_name !== '' && $amount !== ''){
            $this->sData = 'https://www.paypal.com/webapps/xorouter?cmd='.$payment_type.'&business='.$email.'&item_name='.$item_name.'&item_number='.$item_id.'&amount='.$amount.'&currency_code='.$currency.'&shipping='.$shipping.'&tax_rate='.$tax_rate;

            $this->sContent = '<div class="row"><div class="col-sm-4">';

                $this->sContent .= '<strong>Payment type:</strong> '.$payment_type.'<br>'.'<strong>Email:</strong> '.$email.'<br>'.'<strong>Item name:</strong> '.$item_name.'<br>'.'<strong>Item id:</strong> '.$item_id.'</div>';
            
            $this->sContent .= '<div class="col-sm-4">';
            
                $this->sContent .= '<strong>Amount:</strong> '.$amount.'<br>'.'<strong>Currency:</strong> '.$currency.'<br>'.'<strong>Shipping:</strong> '.$shipping.'<br>'.'<strong>Tax rate:</strong> '.$tax_rate.'</div>';
                
            $this->sContent .= '</div>';

            $this->addQrcode("paypal");
        }
        else
            $this->requiredFieldsError();
    }
    
    /**
     * create a qr code of type "bitcoin"
     * @string address -> required
     * @int amount -> required
     * @string label
     * @string message
     */
    public function bitcoinQrcode($address, $amount, $label, $message)
    {
        $address = $this->normalizeInput($address);
        $amount = $this->normalizeInput($amount);
        $label = $this->normalizeInput($label);
        $message = $this->normalizeInput($message);

        if($address !== '' && $amount !== ''){
            $this->sData = 'bitcoin:'.$address.'?amount='.$amount.'&label='.$label.'&message='.$message;
            $this->sContent = '<strong>BTC address:</strong> '.$address.'<br>'.'<strong>Amount:</strong> '.$amount.'<br>';
            $this->sContent .= '<strong>Label:</strong> '.$label.'<br>'.'<strong>Message:</strong> '.$message;

            $this->addQrcode("bitcoin");
        }
        else
            $this->requiredFieldsError();
    }
    
    /**
     * create a qr code of type "2FA"
     * @string algorithms -> required
     * @string secret -> required
     * @string label -> required
     * @string issuer
     * otpauth://TYPE/LABEL?PARAMETERS
     * otpauth://totp/Example:alice@google.com?secret=JBSWY3DPEHPK3PXP&issuer=Example
     */
    public function twofaQrcode($algorithms, $secret, $label, $issuer)
    {
        $algorithms = $this->normalizeInput($algorithms);
        $secret = $this->normalizeInput($secret);
        $label = $this->normalizeInput($label);
        $issuer = $this->normalizeInput($issuer);

        if($algorithms !== '' && $secret !== '' && $label !== ''){
            $this->sData = 'otpauth://' . $algorithms . '/' . $label . '?secret=' . $secret;

            if (!empty($issuer)) {
                $this->sData .= '&issuer=' . $issuer;
            }

            $this->sContent = '<strong>Type:</strong> ' . $algorithms . '<br>'.'<strong>Secret:</strong> ' . $secret . '<br>';
            $this->sContent .= '<strong>Label:</strong> ' . rawurldecode($label);

            if (!empty($issuer)) {
                $this->sContent .= '<br><strong>Issuer:</strong> '. rawurldecode($issuer);
            }

            $this->addQrcode("2fa");
        }
        else
            $this->requiredFieldsError();
    }

    public function getQrcode($id) {
        return $this->qrcode_instance->getQrcode($id);
    }
    
    /**
     * Add qr code
     * Check out http://goqr.me/api/ for more information
     * We save the file obtained with the chosen name and in the selected folder
     * We save into db the url of qrcode image
     */
    private function addQrcode($type) {
        $owner = isset($_POST['id_owner']) ? $this->normalizeInput($_POST['id_owner']) : '';

        if($owner !== "")
            $data_to_db['id_owner'] = $owner;
        else
            $data_to_db['id_owner'] = NULL;

        $input_data['id_owner'] = $owner;
        $data_to_db['created_at'] = date('Y-m-d H:i:s');
        $data_to_db['created_by'] = $_SESSION['user_id'];
        $data_to_db['filename'] = $this->normalizeInput($_POST['filename'] ?? '');
        $data_to_db['created_at'] = date('Y-m-d H:i:s');
        $data_to_db['type'] = $type;
        $data_to_db['format'] = $this->normalizeInput($_POST['format'] ?? '');
        $data_to_db['qrcode'] = $data_to_db['filename'].'.'.$data_to_db['format'];
        $data_to_db['content'] = htmlspecialchars($this->sContent, ENT_QUOTES, 'UTF-8');

        if(isset($_POST['level']))
            $input_data["level"] = $this->normalizeInput($_POST['level']);

        if(isset($_POST['size']))
            $input_data["size"] = $this->normalizeInput($_POST['size']);

        $input_data["foreground"] = $this->normalizeInput($_POST['foreground'] ?? '');
        $input_data["background"] = $this->normalizeInput($_POST['background'] ?? '');

        $data_to_qrcode = urlencode($this->sData);

        $this->qrcode_instance->addQrcode($input_data, $data_to_db, $data_to_qrcode);
    }
    
    /**
     * Edit qr code
     * 
     */
    public function editQrcode($input_data) {
        $owner = isset($input_data['id_owner']) ? $this->normalizeInput($input_data['id_owner']) : '';

        if($owner !== "")
            $data_to_db['id_owner'] = $owner;
        else
            $data_to_db['id_owner'] = NULL;
        $data_to_db['filename'] = $this->normalizeInput($input_data['filename'] ?? '');
        $data_to_db['created_at'] = date('Y-m-d H:i:s');

        $input_data['filename'] = $data_to_db['filename'];

        if(isset($input_data['old_filename'])) {
            $input_data['old_filename'] = $this->normalizeInput($input_data['old_filename']);
        }

        if(isset($input_data['id'])) {
            $input_data['id'] = $this->normalizeInput($input_data['id']);
        }

        $this->qrcode_instance->editQrcode($input_data, $data_to_db);
    }
    
    /**
     * Delete qr code
     * 
     */
    public function deleteQrcode($id, $async = false) {
        if($_SESSION['type'] === "super") {
            $this->qrcode_instance->deleteQrcode($id, $async);
        } else if ($_SESSION['type'] === "admin") {
            $qrcode = $this->getQrcode($id);

            if(!isset($qrcode["id_owner"]))
                $this->failure("You cannot delete this qrcode");

            require_once BASE_PATH . '/lib/Users/Users.php';
            $users = new Users();
            $user = $users->getUser($_SESSION['user_id']);

            if($user["id"] === $qrcode["id_owner"])
                $this->qrcode_instance->deleteQrcode($id, $async);
            else
                $this->failure("You cannot delete this qrcode because it's of another user");
        }
    }
    
    /**
     * Flash message Failure process
     */
    private function failure($message) {
        $_SESSION['failure'] = $message;
        header('Location: static_qrcodes.php');
    	exit();
    }
    
    /**
     * Flash message Success process
     */
    private function success($message) {
        $_SESSION['success'] = $message;
        header('Location: static_qrcodes.php');
    	exit();
    }
    
    /**
     * Flash message Info process
     */
    private function info($message) {
        $_SESSION['info'] = $message;
        header('Location: static_qrcodes.php');
    	exit();
    }
    
    /**
     * Error message if not filled in all the fields required by the type of the qr code
     */
    private function requiredFieldsError() {
        $this->failure('The qr code cannot be created if you do not fill in all the required fields (*)');
    }

    public function debug($data) {
        echo '<pre>' . var_export($data, true) . '</pre>';
        exit();
    }
}
?>
