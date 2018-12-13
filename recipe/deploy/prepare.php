<?php
/* (c) Anton Medvedev <anton@medv.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Deployer;

use function Deployer\Support\str_contains;

desc('Preparing host for deploy');
task('deploy:prepare', function () {
    // Check if shell is POSIX-compliant
    $result = runDocker('echo $0');
    if (!str_contains($result, 'bash') && !str_contains($result, 'sh')) {
        throw new \RuntimeException(
            'Shell on your server is not POSIX-compliant. Please change to sh, bash or similar.'
        );
    }

    run('if [ ! -d {{local_deploy_path}} ]; then mkdir -p {{local_deploy_path}}; fi');

    // Check for existing /current directory (not symlink)
    $result = testDocker('[! -L {{deploy_path}}/current ] && [ -d {{deploy_path}}/current ]');
    if ($result) {
        throw new \RuntimeException('There already is a directory (not symlink) named "current" in ' . get('deploy_path') . '. Remove this directory so it can be replaced with a symlink for atomic deployments.');
    }

    // Create metadata .dep dir.
    run("cd {{local_deploy_path}} && if [ ! -d .dep ]; then mkdir .dep; fi");
    
    // Create releases dir.
    run("cd {{local_deploy_path}} && if [ ! -d releases ]; then mkdir releases; fi");
    
    // Create shared dir.
    run("cd {{local_deploy_path}} && if [ ! -d shared ]; then mkdir shared; fi");
});
