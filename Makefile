# Makefile for Laravel + Docker development environment

.PHONY: init up down restart logs bash mysql permissions clear cache_all artisan composer migrate

##########################
# 初回セットアップ用
##########################
init:
	@echo "=== Docker コンテナをビルド＆起動 ==="
	docker-compose up -d --build
	@echo "=== Laravel 10 プロジェクト作成 ==="
	docker-compose exec php composer create-project "laravel/laravel=10.*" . --prefer-dist
	@echo "=== .env 設定（必要なら自分で編集してください）とアプリキー生成 ==="
	docker-compose exec php cp .env.example .env || true
	docker-compose exec php php artisan key:generate
	@echo "=== ストレージへのシンボリックリンク作成 ==="
	docker-compose exec php php artisan storage:link || true
	@echo "=== 初回セットアップ完了 ==="

##########################
# 日常的に使うコマンド
##########################

# コンテナ起動
up:
	docker-compose up -d

# コンテナ停止
down:
	docker-compose down

# コンテナ再起動
restart:
	docker-compose restart

# コンテナログを確認
logs:
	docker-compose logs -f

# PHP コンテナに入る（bash）
bash:
	docker-compose exec php bash || true

# MySQL コンテナに入る
mysql:
	docker-compose exec mysql bash || true

##########################
# Laravel 権限・キャッシュ操作
##########################

# 権限を正しく設定
permissions:
	docker-compose exec php chown -R www-data:www-data storage
	docker-compose exec php chown -R www-data:www-data bootstrap/cache
	docker-compose exec php chmod -R 775 storage
	docker-compose exec php chmod -R 775 bootstrap/cache

# Laravel キャッシュ・コンフィグをクリア
clear:
	docker-compose exec php php artisan config:clear
	docker-compose exec php php artisan cache:clear
	docker-compose exec php php artisan route:clear
	docker-compose exec php php artisan view:clear

# 権限設定 + キャッシュクリアを一度に実行
cache_all: permissions clear

##########################
# 補助コマンド
##########################

# artisan 実行例
artisan:
	docker-compose exec php php artisan $(cmd)

# composer 実行例
composer:
	docker-compose exec php composer $(cmd)

# マイグレーション
migrate:
	docker-compose exec php php artisan migrate
