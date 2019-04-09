<?php

namespace Deployer;

require 'recipe/symfony4.php';

set('application', 'groomingchimps:api');
set('repository', 'git@bitbucket.org:groomingchimps/api.git');
set('git_tty', true);
set('keep_releases', 3);
set('shared_dirs', ['var/log', 'var/sessions', 'config/jwt', 'vendor']);
set('writable_dirs', ['var']);
set('composer_action', 'install');
set('composer_options', '{{composer_action}} --verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader --no-suggest');

host('api.groomingchimps.titomiguelcosta.com')
    ->user('ubuntu')
    ->stage('prod')
    ->set('deploy_path', '/mnt/websites/groomingchimps/api')
    ->set('shared_files', ['.env.prod.local'])
    ->set('http_user', 'ubuntu')
    ->set('writable_mode', 'acl')
    ->set('branch', 'master')
    ->set('env', ['APP_ENV' => 'prod']);

after('deploy:failed', 'deploy:unlock');
before('deploy:symlink', 'database:migrate');
