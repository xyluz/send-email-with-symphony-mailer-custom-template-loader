<?php namespace App\SharedClasses;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use \Symfony\Component\Mime\Email as SymfonyEmail;

class Email {

    protected Mailer $mailer;
    protected SymfonyEmail $mail;
    public string $template;
    public string $subject;
    public string $message;
    public ?string $link;
    public string $user_name;
    public string $user_email;

    public function __construct(
        public string $type
    )
    {
        $this->startup();
    }


    public function send(){

        $this->loadTemplate();

        $this->mail->html($this->template);

        try{
             $this->mailer->send($this->mail);

        } catch (TransportExceptionInterface $e) {
            echo "Message could not be sent. Mailer Error" . $e->getMessage();
        }
    }

    public function subject(string $subject):static{
        $this->subject = $subject;
        $this->mail->subject($subject);
        return $this;
    }

    public function message(string $body):static{
        $this->message = $body;
        $this->mail->text($body);
        return $this;
    }

    public function link(string $link):static{
        $this->link = $link;
        return $this;
    }

    public function recipient(string $email, string $name):static{
        $this->user_email = $email;
        $this->user_name = $name;
        $this->mail->addTo($email);
        return $this;
    }

    public function cc(string $email, string $name):static{
        $this->mail->addCC($email, $name);
        return $this;
    }

    public function bcc(string $email, string $name):static{
        $this->mail->addBCC($email, $name);
        return $this;
    }

    private function startup(): void
    {
        $transport = Transport::fromDsn("smtp://".$_ENV['EMAIL_USER'].":". $_ENV['EMAIL_PASS'] . "@".$_ENV['EMAIL_HOST'] .":".$_ENV['EMAIL_PORT']);
        $this->mailer = new Mailer($transport);
        $this->mail = (new SymfonyEmail());

        $this->mail->from("seyi@product.com");
        $this->mail->replyTo("seyi@product.com");
        $this->link = null;

    }

    private function loadTemplate():void{

        $this->template = file_get_contents( "./views/templates/email/{$this->type}.html");
        $this->template = str_replace('{NAME}', $this->user_name, $this->template);
        $this->template = str_replace('{EMAIL}', $this->user_email, $this->template);
        $this->template = str_replace('{TITLE}', $this->subject, $this->template);
        $this->template = str_replace('{MESSAGE}', $this->message, $this->template);

        if($this->link) {
            $this->template = str_replace('{LINK}', $this->link, $this->template);
        }

    }


}
