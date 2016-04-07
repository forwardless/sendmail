Send email
----------

## Examples

#### Send message with attach through the mail() 
```php
$mailer = new Mailer(new MailTransport());
$message = (new Message())
            ->to($listTo[1], 'Jon')
            ->from($listTo[0], 'Jon Mailer')
            ->subject('Test subject. Send mail php')
            ->body('<div style="color: red">Test content. Send mail php</div>', 'text/html')
            ->attach($listFiles[0], ['name' => 'main-logo.png', 'mime_type' => 'image\png']);

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
    ->to($listTo[0], 'Олег')
    ->to($listTo[1], 'Иван')
    ->to($listTo[2], 'Сергей')
    ->from($listFrom[0], 'Сергей')
    ->subject('Тема на кирилице. Send mail php')
    ->body('<div style="color: red">Текст письма.<br> Писмо отправелно без attach. <br> Send mail php</div>', 'text/html');
    
$messageSwift = (new SwiftMessageAdapter(new \Swift_Message()))
    ->to($listTo[0], 'Max')
    ->to($listTo[1], 'Ivan')
    ->to($listTo[2], 'Viktor')
    ->from($listFrom[0], 'Bwt')
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