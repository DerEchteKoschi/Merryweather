<?php

namespace App\Controller;

use App\Repository\CrontabRepository;
use Cron\CronExpression;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class CronController extends AbstractController
{
    /**
     * @throws \Exception
     */
    #[Route('/cron', name: 'app_cron')]
    public function index(CrontabRepository $crontabRepository, KernelInterface $kernel): Response
    {
        $app = new Application($kernel);
        $app->setAutoExit(false);
        $crontabs = $crontabRepository->findAll();
        $now = new DateTimeImmutable();
        foreach ($crontabs as $crontab) {
            $cron = new CronExpression($crontab->getExpression());
            if ($crontab->getNextExecution() === null) {
                $crontab->setNextExecution(DateTimeImmutable::createFromMutable($cron->getNextRunDate()));
            } elseif ($crontab->getNextExecution() <= $now) {
                $argv = [];
                if (!empty($crontab->getArguments())) {
                    $argv = explode(' ', $crontab->getArguments());
                }
                array_unshift($argv, '', $crontab->getCommand());
                $input = new ArgvInput($argv);
                $cmd = $app->find($crontab->getCommand());
                $cmd->mergeApplicationDefinition();
                $input->bind($cmd->getDefinition());

                $output = new BufferedOutput();
                $output->setDecorated(false);
                $app->run($input, $output);

                // return the output, don't use if you used NullOutput()
                $content = $output->fetch();
                $crontab->setResult($content);
                $crontab->setLastExecution(new DateTimeImmutable());
                $crontab->setNextExecution(DateTimeImmutable::createFromMutable($cron->getNextRunDate()));
            }
            $crontabRepository->save($crontab, true);
        }

        return new Response('done');
    }
}
