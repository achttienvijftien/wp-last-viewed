# WordPress Last Viewed
Show last viewed WordPress posts.

## Prerequisites

- Docker
- nvm
- yarn
- Composer

## Set-up

- Run `nvm use`
- Run `yarn dev`

## To-do

- [ ] Add client-sided tracking so this plugin may also work with proxy caching + add setting in admin to choose between serverside and clientside;
- [ ] Add option to choose which (custom) post types should be tracked + provide tracking for selected post types;
- [ ] Make a Gutenberg block which shows an overview of "last viewed" (see View class);
- [ ] Add a setting to shortcode & Gutenberg block (if possible) to set the amount of posts shown + implement in View class.

## Development guidelines

Please note the following:

- Use [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/);
- We're doing [Trunk Based Development](https://trunkbaseddevelopment.com) (main being trunk) with [short lived feature branches](https://trunkbaseddevelopment.com/short-lived-feature-branches/);
- Commit messages should follow (conventional commits)[https://www.conventionalcommits.org/en/v1.0.0/];
- [Keep a changelog](https://keepachangelog.com/en/1.0.0/);
- Tests should be written preferably.