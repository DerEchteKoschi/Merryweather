<?php

namespace App\Controller;

use App\Merryweather\AppConfig;
use App\Merryweather\SymfonyCli;
use App\Repository\CrontabRepository;
use Cron\CronExpression;
use DateTimeImmutable;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CronController extends AbstractController implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * @throws \Exception
     */
    #[Route('/cron', name: 'app_cron')]
    public function index(AppConfig $config, CrontabRepository $crontabRepository, SymfonyCli $cli): Response
    {
        if ($config->isCronActive()) {
            $this->logger->notice('webcron started');
            $crontabs = $crontabRepository->findAll();
            $now = new DateTimeImmutable();
            $execCount = 0;
            foreach ($crontabs as $crontab) {
                $cron = new CronExpression($crontab->getExpression());
                if ($crontab->getNextExecution() === null) {
                    $this->logger->info(sprintf('job [%s] not due, skipped', $crontab->getCommand()));
                    $crontab->setNextExecution(DateTimeImmutable::createFromMutable($cron->getNextRunDate()));
                } elseif ($crontab->getNextExecution() <= $now) {
                    $execCount++;
                    $this->logger->notice(sprintf('job [%s] started', $crontab->getCommand()));
                    $content = $cli->run($crontab->getCommand(), $crontab->getArguments());
                    $crontab->setResult($content);
                    $crontab->setLastExecution(new DateTimeImmutable());
                    $crontab->setNextExecution(DateTimeImmutable::createFromMutable($cron->getNextRunDate()));
                }
                $crontabRepository->save($crontab, true);
            }
            $this->logger->notice(sprintf('webcron finished, executed %s job(s)', $execCount));
        }

        return new Response('done');
    }
}
