<?php

namespace App\Support\MesajPaneli;

use \Exception as AuthenticationException;
use \Exception as SmsException;

class UserActions
{
	private $credentialsArray;

	private $endpoint;

	/**
	 * UserActions constructor.
	 *
	 * @param Credentials $credentials
	 */
	public function __construct( Credentials $credentials ) {
		$this->credentialsArray = $credentials->getAsArray();
		$this->endpoint = "api.mesajpaneli.com/json_api";
	}

	/**
	 * Add new group to address book
	 *
	 * @param string $title
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function createAddressBook( $title ) {

		if ( ! $title ) {
			throw new AuthenticationException( "Yeni grup ismi boş olamaz." );
		}

		if ( in_array( $title, $this->getUser()->getSenders() ) ) {
			throw new AuthenticationException( "Bu ($title) isimde bir grup zaten bulunmaktadır." );
		}

		$this->credentialsArray['groupName'] = $title;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/createGroup', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Returns the user object
	 *
	 * Expected array:
	 * $this->credentialsArray = [ 'name' => 'kullaniciAdi', 'pass' => 'sifre' ];
	 *
	 * Successful response upon sending correct credentials:
	 * {"userData":{"musteriid":"12345678","bayiid":"2415","musterikodu":"Demo","yetkiliadsoyad":"Demo","firma":"Demo","orjinli":"0","sistem_kredi":"0","basliklar":["850"]},"status":true}
	 *
	 * Failed response upon wrong credentials:
	 * {"status":false,"error":"Hatali kullanici adi, sifre girdiniz. Lutfen tekrar deneyiniz."}
	 *
	 * @return User
	 * @throws AuthenticationException
	 */
	public function getUser() {
		$userInfo = json_decode( base64_decode( $this->doCurl( $this->endpoint . '/login', $this->encode() ) ), true );

		if ( ! $userInfo['status'] ) {
			$message = ( $userInfo['error'] !== '' ) ? $userInfo['error'] : 'Hatalı cevap alındı. Kullanıcı bilgilerini kontrol edin.';
			throw new AuthenticationException( $message );
		}

		return new User( $userInfo );
	}

	/**
	 * Curl request
	 *
	 * @param $endpoint
	 * @param $postFields
	 *
	 * @return string
	 */
	private function doCurl( $endpoint, $postFields ) {
		Curl::fetch( $endpoint,
			[
				CURLOPT_USERAGENT      => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36",
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_POST           => 1,
				CURLOPT_POSTFIELDS     => $postFields,
				CURLOPT_TIMEOUT        => 50,
				CURLOPT_ENCODING       => '',
				CURLOPT_HEADERFUNCTION => [ 'App\Support\MesajPaneli\Curl', 'head' ],
				CURLOPT_WRITEFUNCTION  => [ 'App\Support\MesajPaneli\Curl', 'body' ]
			]
		);

		return Curl::$body;
	}

	/**
	 * Encodes credentialsArray as data to be sent over Curl
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	private function encode() {
		if ( ! $this->credentialsArray ) {
			throw new AuthenticationException( "Giriş bilgilerinin config.json dosyasinda varlığını kontrol edin." );
		}

		return "data=" . base64_encode( json_encode( $this->credentialsArray ) );
	}

	/**
	 * Decode and check the JSON response
	 *
	 * @param string $base64Decoded
	 * @param null|string $column
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	private function checkJSON( $base64Decoded, $column = null ) {

		$decoded = json_decode( $base64Decoded, true );

		if ( json_last_error() || $decoded['status'] === false ) {
			throw new AuthenticationException( ( $decoded['error'] ) ? "Error: " . $decoded['error'] : 'Girilen bilgileri kontrol ediniz' );
		}

		if ( $column )
			$decoded = $decoded[ $column ];

		return ( json_last_error() ) ? "" : $decoded;
	}

	/**
	 * Remove a group from address book
	 *
	 * @param int $id
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function deleteAddressBook( $id ) {

		if ( ! $id ) {
			throw new AuthenticationException( "Grup id boş olamaz." );
		}

		if ( ! $this->searchForId( $id, $this->getAddressBooks() ) ) {
			throw new AuthenticationException( "Telefon defterinizde bu ($id) IDye sahip bir grup bulunmamaktadır." );
		}

		$this->credentialsArray['groupID'] = $id;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/deleteGroup', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Search for value in multidimensional array
	 *
	 * @param $id
	 * @param $array
	 *
	 * @return bool|null
	 */
	private function searchForId( $id, $array ) {
		foreach ( $array as $key => $val ) {
			if ( $val['id'] == $id ) {
				return true;
			}
		}
		return null;
	}

	/**
	 * Returns all address books of logged in user
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function getAddressBooks() {
		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/getGroups', $this->encode() ) );

		return $this->checkJSON( $base64Decoded, 'groupList' );
	}

	/**
	 * Add a contact/phone number to an address book group
	 *
	 * @param int $groupID
	 * @param array $rows
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function addContact( $groupID, $rows ) {
		if ( ! $groupID || ! is_array( $rows ) || count( $rows ) < 1 ) {
			throw new AuthenticationException( "Kişi eklemek istediğiniz grup IDsi ve kişi bilgileri arrayini dolu gönderdiğinize emin olun." );
		}

		$this->credentialsArray['groupID'] = $groupID;
		$this->credentialsArray['rows'] = $rows;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/addContact', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Remove a contact from an address book group
	 *
	 * @param int $groupID
	 * @param array $rows
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function removeContact( $groupID, $rows ) {
		if ( ! $groupID || ! is_array( $rows ) || count( $rows ) < 1 || ! isset( $rows['numara'] ) ) {
			throw new AuthenticationException( "Numara çıkarmak istediğiniz grup IDsi ve numara arrayini dolu gönderdiğinize emin olun." );
		}

		$this->credentialsArray['groupID'] = $groupID;
		$this->credentialsArray['numara'] = $rows['numara'];

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/removeContact', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Get all contacts in a group
	 *
	 * @param int $groupID
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function getContactsByGroupID( $groupID ) {
		if ( ! $groupID ) {
			throw new AuthenticationException( "Grup id boş olamaz." );
		}

		$this->credentialsArray['groupID'] = $groupID;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/getContactsByGroupID', $this->encode() ) );

		return $this->checkJSON( $base64Decoded, 'NumberList' );
	}

	/**
	 * Search a phone number in all address book groups
	 *
	 * @param string $number
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function searchNumberInGroups( $number ) {
		if ( ! $number ) {
			throw new AuthenticationException( "Aranacak numara boş olamaz." );
		}

		$this->credentialsArray['numara'] = $number;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/searchNumberInGroups', $this->encode() ) );

		return $this->checkJSON( $base64Decoded, 'NumberInfo' );
	}

	/**
	 * Search a phone number in an address book group
	 *
	 * @param string $number
	 * @param int $groupID
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function searchNumberInGroup( $number, $groupID ) {
		if ( ! $number || ! $groupID ) {
			throw new AuthenticationException( "Aranacak numara ve grup ID boş olamaz." );
		}

		$this->credentialsArray['numara'] = $number;
		$this->credentialsArray['groupID'] = $groupID;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/searchNumberInGroup', $this->encode() ) );

		return $this->checkJSON( $base64Decoded, 'NumberInfo' );
	}

	/**
	 * Edit contact details by phone number
	 *
	 * @param int $groupID
	 * @param string $number
	 * @param array $changes
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function editContactByNumber( $groupID, $number, $changes ) {
		if ( ! $number || ! $groupID || ! is_array( $changes ) || count( $changes ) < 1 ) {
			throw new AuthenticationException( "Grup IDsi, kişiye ait telefon numarası ve değiştirmek istediğiniz kişi bilgilerini dolu gönderdiğinize emin olun." );
		}

		$this->credentialsArray['groupID'] = $groupID;
		$this->credentialsArray['search'] = $number;
		$this->credentialsArray['changes'] = $changes;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/editContactByNumber', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Edit contact details by id
	 *
	 * @param int $groupID
	 * @param int $contactID
	 * @param array $changes
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function editContactById( $groupID, $contactID, $changes ) {
		if ( ! $contactID || ! $groupID || ! is_array( $changes ) || count( $changes ) < 1 ) {
			throw new AuthenticationException( "Grup IDsi, kişi IDsi ve değiştirmek istediğiniz kişi bilgilerini dolu gönderdiğinize emin olun." );
		}

		$this->credentialsArray['groupID'] = $groupID;
		$this->credentialsArray['search'] = $contactID;
		$this->credentialsArray['changes'] = $changes;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/editContactById', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Refund credits
	 *
	 * @param int $ref
	 *
	 * @return string
	 * @throws AuthenticationException Refund requires reference number
	 */
	public function refund( $ref ) {
		if ( ! $ref ) {
			throw new AuthenticationException( "Iade işlemi için referans no gereklidir." );
		}

		$this->credentialsArray['refno'] = $ref;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/refund', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Reset user password
	 *
	 * Expected array:
	 * $this->credentialsArray = [ 'name' => 'username', 'pass' => 'oldPassword', 'newpass' => 'newPassword' ];
	 *
	 * @param string $newPassword
	 *
	 * @return string
	 * @throws AuthenticationException
	 *
	 * */
	public function resetPassword( $newPassword ) {
		if ( ! $newPassword ) {
			throw new AuthenticationException( "Yeni şifre boş olamaz." );
		}

		if ( $newPassword == $this->credentialsArray['user']['pass'] ) {
			throw new AuthenticationException( "Eski şifrenizden farklı bir yeni şifre seçiniz." );
		}

		$this->credentialsArray['user']['newpass'] = $newPassword;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/password', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Send bulk SMS
	 *
	 * @param string $baslik
	 * @param TopluMesaj|array $data
	 * @param bool $tr
	 * @param null|int $gonderimZamani
	 *
	 * @return string
	 * @throws SmsException
	 */
	public function bulkSMS( $baslik, $data, $tr = false, $gonderimZamani = null ) {
		if ( ! $baslik ) {
			$baslik = '850';
		}

		if ( ! strlen( $baslik ) >= 3 ) {
			throw new SmsException( "Başlık minimum 3 karakterden oluşmalıdır." );
		}

		$this->credentialsArray['msgBaslik'] = $baslik;

		if ( ! $data ) {
			throw new SmsException( "SMS gönderilecek numaralar ve gönderilmek istenen mesajı doğru gönderdiğinize emin olun." );
		}

		if ( is_object( $data ) && get_class( $data ) == 'TopluMesaj' ) {
			$data = $data->getAsArray();
		}

		if ( ! is_array( $data ) || ! isset( $data['tel'] ) || ! isset( $data['msg'] ) || ! is_array( $data['tel'] ) ) {
			throw new SmsException( "SMS gönderilecek numaralar ve gönderilmek istenen mesajı doğru gönderdiğinize emin olun." );
		}

		$this->credentialsArray['msgData'][] = $data;

		if ( $gonderimZamani && $this->isValidTimeStamp( $gonderimZamani ) ) {
			$this->credentialsArray['start'] = $gonderimZamani;
		}

		if ( $tr ) {
			$this->credentialsArray['tr'] = $tr;
		}

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/api', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Checks if given timestamp is valid
	 *
	 * @param $timestamp
	 *
	 * @return bool
	 */
	private function isValidTimeStamp( $timestamp ) {
		return ( (string) (int) $timestamp === $timestamp )
			&& ( $timestamp <= PHP_INT_MAX )
			&& ( $timestamp >= ~PHP_INT_MAX );
	}

	/**
	 * Send Parametric SMS
	 *
	 * @param $baslik
	 * @param null|array $data
	 * @param bool $tr
	 * @param null|int $gonderimZamani
	 * @param bool $unique
	 *
	 * @return string
	 * @throws SmsException
	 */
	public function parametricSMS( $baslik, $data = null, $tr = false, $gonderimZamani = null, $unique = true ) {
		if ( ! $baslik ) {
			$baslik = '850';
		}

		if ( ! strlen( $baslik ) >= 3 ) {
			throw new SmsException( "Başlık minimum 3 karakterden oluşmalıdır." );
		}

		$this->credentialsArray['msgBaslik'] = $baslik;

		if ( ! $data || ! is_array( $data ) || ! count( $data ) ) {
			throw new SmsException( "SMS gönderilecek numaralar ve gönderilmek istenen mesajı doğru gönderdiğinize emin olun." );
		}

		$this->credentialsArray['msgData'] = $data;

		if ( $gonderimZamani && $this->isValidTimeStamp( $gonderimZamani ) ) {
			$this->credentialsArray['start'] = $gonderimZamani;
		}

		if ( $unique ) {
			$this->credentialsArray['unique'] = $unique;
		}

		if ( $tr ) {
			$this->credentialsArray['tr'] = $tr;
		}

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/api', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * All reports
	 *
	 * @param null|array $tarihler
	 * @param null|int $limit
	 *
	 * @return string
	 */
	public function listReports( $tarihler = null, $limit = null ) {
		if ( $tarihler ) {
			$this->credentialsArray['tarih'] = $tarihler;
		}

		if ( $limit ) {
			$this->credentialsArray['limit'] = $limit;
		}

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/report', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Report details by reference id
	 *
	 * @param $ref
	 * @param null|bool $dates
	 * @param null|bool $operators
	 *
	 * @return string
	 * @throws SmsException
	 */
	public function reportDetails( $ref, $dates = null, $operators = null ) {
		if ( ! $ref ) {
			throw new SmsException( "Referans numarası gereklidir." );
		}

		if ( $dates ) {
			$this->credentialsArray['dates'] = $dates;
		}

		if ( $operators ) {
			$this->credentialsArray['operators'] = $operators;
		}

		$this->credentialsArray['refno'] = $ref;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/report', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}
}