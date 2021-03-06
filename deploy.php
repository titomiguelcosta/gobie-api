<?php

namespace Deployer;

require 'recipe/symfony4.php';

set('application', 'gobie:api');
set('repository', 'git@github.com:titomiguelcosta/gobie-api.git');
set('git_tty', false);
set('keep_releases', 3);
set('shared_dirs', ['var/log', 'var/sessions', 'config/jwt', 'vendor']);
set('writable_dirs', ['var/log', 'var/cache', 'var/sessions']);
set('writable_mode', 'acl');
set('composer_action', 'install');
set('composer_options', '{{composer_action}} --verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader --no-suggest');

host('api.gobie.titomiguelcosta.com')
    ->user('ubuntu')
    ->stage('prod')
    ->set('deploy_path', '/mnt/websites/groomingchimps/api')
    ->set('shared_files', ['.env.local'])
    ->set('branch', 'github-checks')
    ->set('env', ['APP_ENV' => 'prod']);

task('workers:restart', function () {
    run('sudo supervisorctl reload');
});

after('deploy:failed', 'deploy:unlock');
before('deploy:symlink', 'database:migrate');
after('deploy:symlink', 'workers:restart');

