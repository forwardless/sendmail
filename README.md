Send email
----------

## Install
```json
composer require pyataks/sendmail
```

### Examples

#### SMTP configuration
```php
$smtpConfig = [
    'host' => 'ssl://smtp.gmail.com',
    'port' => 465,
    'username' => 'username@gmail.com',
    'password' => ''
];
```

#### Mandrill configuration
```php
$mandrillKey = 'mandrill API-key';
```

#### Send message with attach through the mail() 
```php
$mailer = new Mailer(new MailTransport());
$message = (new Message())
            ->to('emailto@example.com', 'Jon')
            ->from('emailfrom@example.com', 'Jon Mailer')
            ->subject('Test subject. Send mail php')
            ->body('<div style="color: red">Test content. Send mail php</div>', 'text/html')
            ->attach('full_filename', ['name' => 'main-logo.png', 'mime_type' => 'image\png']);

echo $mailer->send($message);
```

#### Create different transports
```php
$mailTransport = new MailTransport();
$smtpTransport = new SMTPTransport($smtpConfig);
$mandrillTransport = new MandrillTransport($mandrillKey);
```
#### Create transport for the mail()
```php
$mailer = new Mailer($mailTransport);
```
#### Prepare messages
```php
$messageCyrillicText = (new Message())
    ->to('emailto1@example.com', 'Рома')
    ->to('emailto2@example.com', 'Маша')
    ->to('emailto3@example.com', 'Кирилл')
    ->from('emailfrom@example.com', 'Джон Сильвер')
    ->subject('Тема на кирилице. Send mail php')
    ->body('<div style="color: red">Текст письма.<br> Писмо отправелно без attach. <br> Send mail php</div>', 'text/html');
    
$messageSwift = (new SwiftMessageAdapter(new \Swift_Message()))
    ->to('emailto1@example.com', 'Рома')
    ->to('emailto2@example.com', 'Маша')
    ->to('emailto3@example.com', 'Кирилл')
    ->from('emailfrom@example.com', 'Джон Сильвер')
    ->subject('Subject English swift . Send mail php')
    ->body('<div style="color: green">Text of the letter.<br> Letter sent WITH attach. <br> Send mail php</div>', 'text/html')
    ->attach($listFiles[0])
    ->attach($listFiles[1]);
```
#### Sending through mail
```php
echo $mailer->send($messageCyrillicText);
```

#### Change transport to SMTP, change subject of prepared mail, and, finally, send
```php
$mailer->setTransport($smtpTransport);

$messageEnglish->subject('Subject English. Send smtp php');
echo $mailer->send($messageEnglish);
```