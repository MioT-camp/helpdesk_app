# ヘルプデスクシステム

顧客からの問い合わせを効率的に管理し、FAQによる自己解決を促進するWebベースのヘルプデスクシステムです。

## 🚀 概要

本システムは、Laravel 12.xとLivewire Voltを活用したモダンなヘルプデスク管理システムです。顧客からの問い合わせの受付から解決まで、一元的に管理することができます。

### 主な機能

- **問い合わせ管理**: 顧客からの問い合わせの受付、分類、担当者割り当て
- **FAQ管理**: よくある質問の作成・編集・公開管理
- **ユーザー管理**: スタッフ・マネージャー・管理者の役割別アクセス制御
- **ダッシュボード**: 問い合わせ状況の可視化と統計情報の表示
- **レポート機能**: 月次・トレンド分析レポート
- **タスク管理**: 内部的なタスク管理機能

## 🛠 技術スタック

### バックエンド
- **PHP**: 8.4+
- **Laravel**: 12.x
- **Livewire Volt**: 1.7.0（Functional Component）
- **データベース**: MariaDB 11

### フロントエンド
- **TailwindCSS**: 4.0.7
- **Vite**: 7.0.4
- **Flux**: 2.1.1（UIコンポーネント）

### 開発環境
- **Laravel Sail**: Docker環境
- **Pest**: テストフレームワーク
- **Laravel Debugbar**: 開発支援

## 📋 要件

- PHP 8.4以上
- Composer
- Node.js & npm
- Docker & Docker Compose（Sail使用時）

## 🚀 インストール

### 1. リポジトリのクローン

```bash
git clone <repository-url>
cd helpdesk_app
```

### 2. 依存関係のインストール

```bash
# Composerパッケージのインストール
composer install

# npmパッケージのインストール
npm install
```

### 3. 環境設定

```bash
# 環境ファイルのコピー
cp .env.example .env

# アプリケーションキーの生成
php artisan key:generate
```

### 4. データベース設定

`.env`ファイルでデータベース設定を確認・編集してください：

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=helpdesk_app
DB_USERNAME=root
DB_PASSWORD=password
```

### 5. データベースのセットアップ

```bash
# マイグレーションの実行
php artisan migrate

# シーダーの実行（開発環境）
php artisan db:seed
```

### 6. アセットのビルド

```bash
# 開発用ビルド
npm run dev

# 本番用ビルド
npm run build
```

### 7. アプリケーションの起動

```bash
# 開発サーバーの起動
php artisan serve

# または Sail使用時
./vendor/bin/sail up
```

## 🐳 Docker（Sail）を使用した開発

### Sailのセットアップ

```bash
# 初回のみ実行
./vendor/bin/sail build --no-cache

# コンテナの起動
./vendor/bin/sail up -d

# 開発環境の一括起動
./vendor/bin/sail dev
```

### 便利なSailコマンド

```bash
# PHPコマンドの実行
./vendor/bin/sail php artisan migrate

# Composerコマンドの実行
./vendor/bin/sail composer install

# npmコマンドの実行
./vendor/bin/sail npm run dev

# テストの実行
./vendor/bin/sail test
```

## 📁 プロジェクト構造

```
helpdesk_app/
├── app/
│   ├── Http/Controllers/     # コントローラー
│   ├── Livewire/Actions/     # Livewireアクション
│   ├── Models/              # Eloquentモデル
│   ├── Policies/            # 認可ポリシー
│   └── Services/            # ビジネスロジック
├── database/
│   ├── migrations/          # データベースマイグレーション
│   └── seeders/            # シーダー
├── resources/
│   ├── views/livewire/     # Voltコンポーネント
│   ├── css/               # スタイルシート
│   └── js/                # JavaScript
├── routes/
│   └── web.php            # Webルート
└── tests/                 # テストファイル
```

## 🗄 データベース設計

### 主要テーブル

- **users**: ユーザー情報（スタッフ・マネージャー・管理者）
- **categories**: FAQカテゴリ
- **faqs**: FAQ（よくある質問）
- **inquiries**: 問い合わせ情報
- **inquiry_histories**: 問い合わせ履歴
- **todos**: タスク管理
- **attachments**: 添付ファイル
- **system_settings**: システム設定

### ユーザーロール

- **admin**: 管理者（全機能アクセス可能）
- **manager**: マネージャー（問い合わせ管理・FAQ管理）
- **staff**: スタッフ（問い合わせ対応）

## 🧪 テスト

```bash
# 全テストの実行
php artisan test

# 特定のテストの実行
php artisan test --filter=FeatureTest

# カバレッジレポートの生成
php artisan test --coverage
```

## 📊 主要機能

### 問い合わせ管理
- 問い合わせの受信・分類・担当者割り当て
- ステータス管理（pending/in_progress/completed/closed）
- 優先度設定（低/中/高/緊急）
- 回答期限管理

### FAQ管理
- カテゴリ別FAQ作成・編集
- タグ機能による分類
- 閲覧回数統計
- 検索機能

### ダッシュボード
- 今日の問い合わせ一覧
- 月次統計
- 未対応問い合わせの確認
- 担当別作業状況

### レポート機能
- 月次レポート
- トレンド分析
- カテゴリ別統計
- 担当者別パフォーマンス

## 🔧 開発

### コード規約
- PSR-12準拠のコードフォーマット
- Livewire VoltのFunctional Component使用
- TailwindCSSによるスタイリング
- 日本語コメント記述

### コマンドエイリアス

```bash
# .bashrc または .zshrc に追加
alias php="./vendor/bin/sail"
alias composer="./vendor/bin/sail composer"
alias npm="./vendor/bin/sail npm"
```

### 開発用コマンド

```bash
# 開発環境の一括起動
composer dev

# コードフォーマット
./vendor/bin/sail pint

# デバッグバーの確認
# ブラウザで http://localhost にアクセス
```

## 🔒 セキュリティ

- CSRF保護
- XSS対策
- SQLインジェクション対策
- 認可制御（Policy使用）
- セッション管理
- 環境変数による機密情報管理

## 📝 ライセンス

MIT License

## 🤝 コントリビューション

1. このリポジトリをフォーク
2. 機能ブランチを作成 (`git checkout -b feature/amazing-feature`)
3. 変更をコミット (`git commit -m 'Add some amazing feature'`)
4. ブランチにプッシュ (`git push origin feature/amazing-feature`)
5. プルリクエストを作成

## 📞 サポート

質問や問題がございましたら、Issueを作成してください。

---

**注意**: 本システムは開発・テスト環境での使用を前提としており、本番環境での使用前に適切なセキュリティ設定とパフォーマンスチューニングを行ってください。
