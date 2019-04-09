<?php

namespace Deployer;

require 'recipe/symfony.php';

set('application', 'groomingchimps:api');
set('repository', 'git@bitbucket.org:groomingchimps/api.git');
set('git_tty', true); 
set('keep_releases', 3);
set('shared_dirs', ['vendor']);
set('writable_dirs', ['var']);

host('api.groomingchimps.titomiguelcosta.com')
    ->stage('production')
    ->set('deploy_path', '/mnt/websites/groomingchimps/prod/api')
    ->set('shared_files', ['.env.prod'])
;
    

task('build', function () {
    run('cd {{release_path}} && build');
});


after('deploy:failed', 'deploy:unlock');
before('deploy:symlink', 'database:migrate');
