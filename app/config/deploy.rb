set :application, "municipales2015.listabierta.com"
set :deploy_user, "muni"
set :domain,      "#{application}"
set :deploy_to,   "/home/#{deploy_user}/public"
set :app_path,    "app"

set :repository,  "git@github.com:listabierta/municipales2015.git"
set :scm,         :git

set :model_manager, "doctrine"

role :web,        domain                         # Your HTTP server, Apache/Nginx/etc
role :app,        domain, :primary => true       # Could be the same as your `Web` server

set  :keep_releases,  3

# Avoid clone the repo in every deploy (use cache)
set :deploy_via, :remote_cache
set   :use_sudo,      false

after "deploy", "deploy:cleanup"

logger.level = Logger::MAX_LEVEL
