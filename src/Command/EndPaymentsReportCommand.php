<?php

namespace App\Command;

use App\Entity\Transaction;
use App\Entity\User;
use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EndPaymentsReportCommand extends Command
{
    private $twig;
    private $mailer;
    private $manager;
    protected static $defaultName = 'payment:ending:notification';

    public function __construct(Environment $twig, MailerInterface $mailer, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->manager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->manager->getRepository(User::class)->findAll();
        $data = [];
        foreach ($users as $user) {
            $data = $this->manager->getRepository(Transaction::class)->findEndingTransaction($user->getId());
            if (count($data) != 0) {
                $htmlMail = $this->twig->render('mail/endPayment.html.twig',['dataForMail' => $data]);
                $message = (new Email())
                    ->to($user->getUserIdentifier())
                    ->from('report-system@study-on')
                    ->subject('Уведомление об окончании срока аренды курсов')
                    ->html($htmlMail);
                try {
                    $this->mailer->send($message);
                } catch (TransportExceptionInterface $e) {
                    $output->writeln($e->getMessage());
                    $output->writeln('Возникла ошибка. Не удалось отправить сообщение');
                    return Command::FAILURE;
                }
            }
        }
        $output->writeln('Письма с оповещениями отправлены');
        return Command::SUCCESS;
    }
}