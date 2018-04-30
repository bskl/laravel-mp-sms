## Mesajpaneli.com için Laravel Notifications Channel

[![StyleCI](https://styleci.io/repos/129779257/shield?branch=master)](https://styleci.io/repos/129779257)

## Yükleme

mesajpaneli.com sitesinin resmi olmayan Laravel 5 Notifications paketidir.

Composer ile yüklemek için:

```php
composer require bskl/laravel-mp-sms
```

Yükeleme tamamlandıktan sonra mp-sms.php dosyasını config klasörüne kopyalamak için aşağıdaki komutu çalıştırın.

```php
php artisan vendor:publish --provider="Bskl\MpSms\ServiceProvider"
```

## Laravel Notifications ile Kullanım

```php
namespace App\Notifications;

use Bskl\MpSms\Notifications\MpSmsChannel;
use Bskl\MpSms\Notifications\MpSmsMessage;
use Illuminate\Notifications\Notification;

class ExampleNotification extends Notification
{
    /**
     * Notification via MpSmsChannel.
     */
    public function via($notifiable)
    {
        return [MpSmsChannel::class];
    }

    /**
     * Get the mesajpaneliapi representation of the notification.
     */
    public function toMpSms($notifiable)
    {
        return (new MpSmsMessage)
                    ->content("Mesaj içeriği");
    }
}
```

Ayrıca, Notifiable modelinize routeNotificationForMpSms() fonksiyonunu eklemelisiniz.

```php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    
    /**
     * Returns the user's phone number.
     */
    public function routeNotificationForMpSms()
    {
        return $this->phone; // Örnek: 1234567890
    }
}
```

## MesajPaneli Hesabınızı Ayarlama

[https://smsvitrini.com](https://smsvitrini.com/ "https://smsvitrini.com/") adresinden aldığınız kullanıcı bilgilerinizi config/mp-sms.php dosyasına kayıt etmelisiniz. Kolaylık olmasını istiyorsanız .env dosyanıza kayıt edebilirsiniz.

```php
return [
    'username' => env('MPSMS_USERNAME', 'username'),
    'password' => env('MPSMS_PASSWORD', 'password'),
    'from'     => env('MPSMS_FROM', 'from'),
];
```

## Lisans

MIT
