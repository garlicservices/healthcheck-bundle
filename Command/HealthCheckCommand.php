<?php

namespace Garlic\HealthCheck\Command;

use Garlic\Bus\Service\CommunicatorService;
use Garlic\HealthCheck\Service\Lock\LockService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;

class HealthCheckCommand extends Command
{
    protected static $defaultName = 'healthcheck:init';

    /** @var int Time until next run would be possible  */
    protected $lockTime;

    /** @var Kernel $kernel */
    protected $kernel;
    /** @var LockService $lockService */
    protected $lockService;

    /**
     * HealthCheckCommand constructor.
     * @param string|null $name
     * @param Kernel $kernel
     */
    public function __construct(?string $name = null, Kernel $kernel, LockService $lockService)
    {
        $this->kernel = $kernel;
        $this->lockService = $lockService->getLockFactory();

        $this->lockTime = getenv('HEALTHCHECK_LOCK_TTL') ?? 30;

        parent::__construct($name);
    }

    /**
     * Description
     */
    protected function configure()
    {
        $this->setDescription('Init healthcheck on container start');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->success('Healthcheck init started.');
        $lock = $this->lockService->createLock(getenv('SERVICE_NAME') . '-healthcheck-lock', $this->lockTime, false);
        if (!$lock->acquire()) {
            $io->error('Process is locked by another daemon. Shutting down.');
            return;
        }

        $container = $this->kernel->getContainer();

        $response = $container->get('http_kernel')
            ->handle(Request::create('/graphql', 'POST', ['query' => $this->getIntrospectionQuery()]), HttpKernelInterface::MASTER_REQUEST);

        $container->get(CommunicatorService::class)
            ->command('gateway')
            ->post()
            ->serviceRebuildSchema([], [
                'data' => $response->getContent(),
                'name' => getenv('SERVICE_NAME'),
                'timing' => 0
            ]);

        $io->success('Init healthcheck message sent.');
    }

    /**
     * Self-introspection
     * @return false|string
     */
    protected function getIntrospectionQuery()
    {
        return file_get_contents(__DIR__ . '/../Resources/query/introspection.graphql');
    }
}
