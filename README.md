# vodweb

轻量级的聚合影视站，整合采集接口并提供快速缓存与简洁前端。

## 简介
- 聚合第三方影视接口，提供首页、分类、搜索、详情、播放等页面。
- 内置文件级缓存与错误回退，提升加载速度与稳定性。
- 播放页令牌化隐藏真实视频地址，m3u8 可通过自定义代理播放。
- 后台管理模块化（admin/ 目录），支持资源接口、m3u8 代理、分类替换/隐藏、源名映射、站点名、账号密码。

## 特性
- 接口文件缓存（JSON/XML）与 TTL 管理；异常回退到过期缓存。
- 播放页令牌 token（隐藏真实链接）、分集列表高亮切换。
- 分类名称映射与隐藏（按名称关键词，按 ID 隐藏已移除）。
- 播放源名称别名映射（如 `lzm3u8 → 线路1`）。
- 后台管理模块化：登录、首页（系统时钟）、设置（站点名/账号密码）、资源管理（API/m3u8 开关、分类与源映射）。

## 更新历史（简短）
- 2511.0110.3904：后台模块化重构（admin/ 目录），拆分为登录/首页/设置/资源管理模块；新增 `site_name` 与 `api_enabled`、`m3u8_enabled` 开关；`admin.php` 作为桥接入口。
- 2511.0109.1501：新增后台管理页与 `settings.json` 持久化；支持采集源、m3u8 代理、分类别名/隐藏（按名称）、播放源名称映射；后台账号密码改为从配置读取。
- 2511.0108.2010：首页精选逻辑内联；性能与体验优化。
- 2511.0107.4532：仅保留 m3u8 分线与链接；播放源名称别名映射。
- 2511.0106.1200：详情简介内容清理；详情页缓存 TTL 调整为 1 小时。
- 2511.0105.0544：播放页令牌化隐藏真实链接，m3u8 代理播放；分集列表与高亮。
- 2511.0104.3022：接口文件缓存与错误回退、压缩传输、超时优化。
- 2511.0103.0000：初始页面与基础结构。

## 目录结构与说明
```
.
├── assets/                # 静态资源（样式、占位图）
├── cache/                 # 接口返回内容的文件缓存目录（自动创建）
├── lib/
│   ├── api.php            # 接口请求、缓存、解析（含分线解析、源名别名）
│   └── categories.php     # 分类获取、前端映射与后台隐藏/别名应用
├── partials/
│   ├── header.php         # 页头（导航、搜索框）
│   └── footer.php         # 页脚
├── config.php             # 站点配置与通用函数、settings.json 读写接口
├── admin.php              # 兼容入口（桥接到 admin/index.php）
├── admin/                 # 后台模块目录
│   ├── index.php          # 后台入口：已登录跳 home，未登录跳 login
│   ├── login.php          # 登录/退出逻辑
│   ├── home.php           # 首页（系统时间）
│   ├── settings.php       # 设置（站点名、账号/密码修改）
│   ├── resources.php      # 资源管理（API/m3u8 开关、分类隐藏与映射、源名映射）
│   └── inc.php            # 公共布局与认证（tabs、样式、admin_require_login）
├── settings.json          # 持久化设置（由后台写入，支持手动编辑）
├── index.php              # 首页（最新更新瀑布流）
├── category.php           # 分类页列表
├── search.php             # 搜索页
├── detail.php             # 详情页（简介清理、剧集列表）
├── play.php               # 播放页（令牌取回真实链接、分集列表与高亮）
└── vodfl.php              # 分类映射与显示名规则（前端默认映射）
```

## 使用说明
- 环境需求：
  - PHP 7.0+（兼容较低版本，推荐 7.0+），开启会话；
  - Web 服务器或 PHP 内置服务器；`cache/` 目录需可写。
- 部署：将源码放入 Web 根目录；按环境配置虚拟主机或直接访问。
- 后台：
  - 新入口：访问 `/admin/`（自动跳转登录/首页）；或直接访问 `/admin/login.php`。
  - 兼容入口：`/admin.php`（桥接到模块化后台）。
  - 默认账号/密码：`admin/admin`，建议登录后在“设置”中修改。

## 配置说明
- 推荐通过后台页面编辑，或直接修改 `settings.json`：
  - `site_name`：站点名称（前台 `<title>` 与 Logo）。
  - `api_base`：采集源基础地址。
  - `api_enabled`：资源接口启用开关（false 时不发起新请求，尝试旧缓存）。
  - `m3u8_proxy`：m3u8 代理前缀（如 `http://anyn.cc/m3u8/?url=`）。
  - `m3u8_enabled`：m3u8 代理启用开关（false 时尝试直接播放）。
  - `category_aliases`：分类名称别名（JSON 对象，按名称关键词匹配）。
  - `category_hide`：分类隐藏关键词（数组，按名称关键词隐藏）。
  - `source_aliases`：播放源别名（JSON 对象）。
  - `admin_user` / `admin_pass`：后台登录凭证。
- 示例 `settings.json`：
```json
{
  "site_name": "vodweb",
  "api_base": "https://cj.lziapi.com/api.php/provide/vod/",
  "api_enabled": true,
  "m3u8_proxy": "http://anyn.cc/m3u8/?url=",
  "m3u8_enabled": true,
  "category_aliases": {"国产剧": "华语剧"},
  "category_hide": ["纪录片", "综艺"],
  "source_aliases": {"lzm3u8": "线路1"},
  "admin_user": "admin",
  "admin_pass": "admin"
}
```

## 常见操作
- 清空缓存：删除 `cache/*.cache` 文件。
- 调整播放页样式：`play.php` 播放器容器（m3u8 为 16:9 自适应）。
- 安全增强建议：使用密码哈希（`password_hash` / `password_verify`）、限制登录失败重试、CSRF 防护。

---
如需自定义 UI、增加来源、或优化解析策略（如剧集去重/排序、跨源合并），可在 `lib/api.php` 与相关页面中直接扩展。
