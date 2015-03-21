set :application, "municipales2015.listabierta.org"
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
set :use_sudo,      false
set :git_enable_submodules, 1
ssh_options[:forward_agent] = true

set :shared_files,      ["app/config/parameters.yml"]
set :shared_children,     [app_path + "/logs", web_path + "/uploads", "vendor", app_path + "/sessions", app_path + "/docs"]
set :use_composer, true
set :update_vendors, true


set :writable_dirs,       ["app/cache", "app/logs", app_path + "/docs"]
set :webserver_user,      "www-data"
set :user, "root" # #{deploy_user}
set :permission_method,   :acl
set :use_set_permissions, true

after "deploy:finalize_update" do
  run "sudo chmod -R 777 #{latest_release}/#{cache_path}"
  run "sudo chown -R #{deploy_user}:#{webserver_user} #{latest_release}/#{cache_path}"
  run "sudo chown -R #{deploy_user}:#{webserver_user} #{latest_release}/#{log_path}"
  run "sudo chown -R #{deploy_user}:#{webserver_user} #{latest_release}"
end

before "deploy:restart", "deploy:set_permissions"
after "deploy", "deploy:cleanup"

logger.level = Logger::MAX_LEVEL
