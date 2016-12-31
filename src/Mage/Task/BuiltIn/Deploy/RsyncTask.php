<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\Deploy;

use Symfony\Component\Process\Process;
use Mage\Task\AbstractTask;

/**
 * Rsync Task - Copy files with Rsync
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class RsyncTask extends AbstractTask
{
    public function getName()
    {
        return 'deploy/rsync';
    }

    public function getDescription()
    {
        return '[Deploy] Copying files with Rsync';
    }

    public function execute()
    {
        $user = $this->runtime->getEnvironmentConfig('user');
        $host = $this->runtime->getWorkingHost();
        $hostPath = rtrim($this->runtime->getEnvironmentConfig('host_path'), '/');
        $targetDir = rtrim($hostPath, '/');

        if ($this->runtime->getEnvironmentConfig('releases', false)) {
            $targetDir = sprintf('%s/releases/%s', $hostPath, $this->runtime->getReleaseId());
        }

        $excludes = $this->getExcludes();
        $cmdRsync = sprintf('rsync -avz %s ./ %s@%s:%s', $excludes, $user, $host, $targetDir);

        /** @var Process $process */
        $process = $this->runtime->runLocalCommand($cmdRsync, 600);
        return $process->isSuccessful();
    }

    protected function getExcludes()
    {
        $excludes = $this->runtime->getEnvironmentConfig('exclude', []);
        $excludes = array_merge(['.git'], $excludes);

        foreach ($excludes as &$exclude) {
            $exclude = '--exclude=' . $exclude;
        }

        return implode(' ', $excludes);
    }
}