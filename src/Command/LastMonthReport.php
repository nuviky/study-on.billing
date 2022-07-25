<?php

namespace App\Command;

use App\Entity\Course;
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

class LastMonthReport extends Command
{
    private $twig;
    private $mailer;
    private $manager;
    protected static $defaultName = 'payment:report';

    public function __construct(Environment $twig, MailerInterface $mailer, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->manager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $transaction = $this->manager->getRepository(Transaction::class)->findLastMonthTransaction();
        if (!is_null($transaction)) {
            $startDate = (new \DateTime())->modify('-1 month')->format('Y-m-d');
            $endDate = (new \DateTime())->format('Y-m-d');
            $htmlMail = $this->twig->render(
                'mail/lastMonthReport.html.twig',
                [
                    'dataForMail' => $transaction,
                    'startDate' => $startDate,
                    'endDate'=> $endDate,
                ]
            );
            $message = (new Email())
                ->to($_ENV['EMAIL_TO_SEND'])
                ->from('report-system@study-on')
                ->subject('Отчет')
                ->html($htmlMail);
            try {
                $this->mailer->send($message);
            } catch (TransportExceptionInterface $e) {
                $output->writeln($e->getMessage());
                $output->writeln('Не удалось отправить сообщение');
                return Command::FAILURE;
            }
        }
        $output->writeln('Отчетом отправлен');
        return Command::SUCCESS;
    }
}