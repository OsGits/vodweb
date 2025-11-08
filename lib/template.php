<?php
/**
 * 模板引擎核心类
 * 实现模板的注册、加载、渲染和版本管理
 */

// 加载模板系统全局配置
require_once __DIR__ . '/../templates/.config/config.php';
class TemplateEngine {
    // 默认模板目录
    const DEFAULT_TEMPLATE_DIR = 'templates';
    const DEFAULT_TEMPLATE_NAME = 'default';
    
    // 模板引擎实例（单例模式）
    private static $instance = null;
    
    // 当前使用的模板
    private $templateName = self::DEFAULT_TEMPLATE_NAME;
    
    // 模板根目录
    private $templateDir = '';
    
    // 当前模板路径
    private $currentTemplatePath = '';
    
    // 模板配置
    private $templateConfig = [];
    
    // 已注册的组件
    private $components = [];
    
    // 已注册的布局
    private $layouts = [];
    
    // 全局变量
    private $globalVars = [];
    
    // 禁止外部实例化
    private function __construct() {
        // 优先使用全局配置中的模板路径
        $globalTemplatePath = template_config('template_path', rtrim(__DIR__, '/') . '/../' . self::DEFAULT_TEMPLATE_DIR);
        $this->templateDir = $globalTemplatePath;
        
        // 使用全局配置中的默认模板名称
        $defaultTemplate = template_config('default_template', self::DEFAULT_TEMPLATE_NAME);
        $this->templateName = $defaultTemplate;
        
        // 确保缓存目录存在
        if (template_config('cache_enabled')) {
            ensure_template_cache_dir();
        }
        
        $this->setTemplate($this->templateName);
    }
    
    // 获取单例实例
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // 设置当前使用的模板
    public function setTemplate($templateName) {
        $this->templateName = $templateName;
        $this->currentTemplatePath = $this->templateDir . '/' . $templateName;
        $this->loadTemplateConfig();
        return $this;
    }
    
    // 加载模板配置
    private function loadTemplateConfig() {
        $configPath = $this->currentTemplatePath . '/config.php';
        if (is_file($configPath)) {
            $this->templateConfig = include($configPath);
        } else {
            // 加载默认配置
            $this->templateConfig = [
                'name' => $this->templateName,
                'version' => '1.0.0',
                'author' => '',
                'description' => '',
                'assets_path' => '/assets',
                'default_layout' => 'main',
                'cache_ttl' => 3600
            ];
        }
    }
    
    // 获取模板配置
    public function getConfig($key = null, $default = null) {
        if ($key === null) {
            return $this->templateConfig;
        }
        return isset($this->templateConfig[$key]) ? $this->templateConfig[$key] : $default;
    }
    
    // 设置全局变量
    public function setGlobal($name, $value) {
        $this->globalVars[$name] = $value;
        return $this;
    }
    
    // 批量设置全局变量
    public function setGlobals(array $vars) {
        foreach ($vars as $name => $value) {
            $this->setGlobal($name, $value);
        }
        return $this;
    }
    
    // 渲染模板
    public function render($template, array $vars = [], $layout = null) {
        // 合并全局变量和局部变量
        $allVars = array_merge($this->globalVars, $vars);
        
        // 生成缓存键
        $cacheKey = $this->templateName . ':' . $template . ':' . md5(serialize($allVars)) . ':' . ($layout ?? 'default');
        
        // 尝试从缓存获取
        if (template_config('cache_enabled')) {
            $cachedContent = $this->fetchFromCache($cacheKey);
            if ($cachedContent !== false) {
                return $cachedContent;
            }
        }
        
        // 如果提供了布局，先渲染内容，再应用布局
        if ($layout !== null) {
            $content = $this->renderTemplate($template, $allVars);
            $layoutVars = $allVars + ['content' => $content];
            $result = $this->renderLayout($layout, $layoutVars);
        } else {
            // 直接渲染模板
            $result = $this->renderTemplate($template, $allVars);
        }
        
        // 存入缓存
        if (template_config('cache_enabled')) {
            $this->storeToCache($cacheKey, $result);
        }
        
        return $result;
    }
    
    // 渲染模板文件
    private function renderTemplate($template, array $vars = []) {
        // 解析模板路径
        $templatePath = $this->resolveTemplatePath($template);
        
        if (!is_file($templatePath)) {
            throw new Exception("Template file not found: {$templatePath}");
        }
        
        // 合并全局变量和局部变量
        $allVars = array_merge($this->globalVars, $vars);
        
        // 提取变量到当前作用域
        extract($allVars, EXTR_SKIP);
        
        // 捕获输出
        ob_start();
        include $templatePath;
        $content = ob_get_clean();
        
        return $content;
    }
    
    // 渲染布局
    private function renderLayout($layout, array $vars = []) {
        // 布局文件路径解析
        $layoutPath = $this->currentTemplatePath . '/layouts/' . $layout . '.php';
        
        if (!is_file($layoutPath)) {
            // 尝试默认布局
            $layoutPath = $this->currentTemplatePath . '/layouts/' . $this->getConfig('default_layout') . '.php';
            if (!is_file($layoutPath)) {
                throw new Exception("Layout file not found: {$layoutPath}");
            }
        }
        
        // 提取变量到当前作用域
        extract($vars, EXTR_SKIP);
        
        // 捕获输出
        ob_start();
        include $layoutPath;
        $content = ob_get_clean();
        
        return $content;
    }
    
    // 解析模板路径
    private function resolveTemplatePath($template) {
        // 支持点语法，如 'partials.header' 会被解析为 'partials/header.php'
        $parts = explode('.', $template);
        $filename = array_pop($parts);
        $directory = implode('/', $parts);
        
        // 从配置获取模板扩展名
        $ext = template_config('template_extension', '.php');
        
        // 尝试在当前模板的不同目录中查找
        $possiblePaths = [
            $this->currentTemplatePath . '/' . $directory . '/' . $filename . $ext,
            $this->currentTemplatePath . '/' . $filename . $ext,
            $this->currentTemplatePath . '/' . template_config('layouts_dir', 'layouts') . '/' . $filename . $ext,
            $this->currentTemplatePath . '/' . template_config('partials_dir', 'partials') . '/' . $filename . $ext,
        ];
        
        foreach ($possiblePaths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        
        // 如果在当前模板中找不到，尝试在默认模板中查找
        $defaultPaths = [
            $this->templateDir . '/default/' . $directory . '/' . $filename . $ext,
            $this->templateDir . '/default/' . $filename . $ext,
            $this->templateDir . '/default/' . template_config('layouts_dir', 'layouts') . '/' . $filename . $ext,
            $this->templateDir . '/default/' . template_config('partials_dir', 'partials') . '/' . $filename . $ext,
        ];
        
        foreach ($defaultPaths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        
        // 如果都找不到，返回默认路径
        return $this->currentTemplatePath . '/' . $directory . '/' . $filename . $ext;
    }
    
    // 注册组件
    public function registerComponent($name, $path) {
        $this->components[$name] = $path;
        return $this;
    }
    
    // 渲染组件
    public function renderComponent($name, array $vars = []) {
        if (!isset($this->components[$name])) {
            // 从配置获取组件目录
            $componentDir = template_config('components_dir', 'components');
            $ext = template_config('template_extension', '.php');
            
            // 尝试从当前模板的组件目录加载
            $componentPath = $this->currentTemplatePath . '/' . $componentDir . '/' . $name . $ext;
            
            // 如果找不到，尝试从默认模板的组件目录加载
            if (!is_file($componentPath)) {
                $componentPath = $this->templateDir . '/default/' . $componentDir . '/' . $name . $ext;
            }
            
            if (!is_file($componentPath)) {
                if (template_config('debug')) {
                    trigger_error("Component not found: {$name}", E_USER_WARNING);
                }
                return '';
            }
            
            $this->registerComponent($name, $componentPath);
        }
        
        // 合并全局变量和局部变量
        $allVars = array_merge($this->globalVars, $vars);
        
        // 提取变量到当前作用域
        extract($allVars, EXTR_SKIP);
        
        // 捕获输出
        ob_start();
        include $this->components[$name];
        $content = ob_get_clean();
        
        return $content;
    }
    
    // 获取可用模板列表
    public function getAvailableTemplates() {
        $templates = [];
        $dir = new DirectoryIterator($this->templateDir);
        
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDir() && !$fileinfo->isDot() && $fileinfo->getFilename() !== '.config') {
                $templateName = $fileinfo->getFilename();
                $templates[] = $templateName;
            }
        }
        
        return $templates;
    }
    
    // 获取模板信息
    public function getTemplateInfo($templateName = null) {
        if ($templateName === null) {
            $templateName = $this->templateName;
        }
        
        $templatePath = $this->templateDir . '/' . $templateName;
        if (!is_dir($templatePath)) {
            return null;
        }
        
        $configPath = $templatePath . '/config.php';
        $config = [
            'name' => $templateName,
            'version' => '1.0.0',
            'author' => '',
            'description' => '',
            'exists' => true
        ];
        
        if (is_file($configPath)) {
            $templateConfig = [];
            include $configPath;
            $config = array_merge($config, $templateConfig);
        }
        
        return $config;
    }
    
    // 清除模板缓存
    public function clearCache() {
        if (!template_config('cache_enabled')) {
            return false;
        }
        
        $cachePath = template_config('cache_path');
        if (is_dir($cachePath)) {
            $files = glob($cachePath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            return true;
        }
        return false;
    }
    
    // 从缓存获取内容
    private function fetchFromCache($key) {
        $cacheFile = template_config('cache_path') . '/' . $key . '.cache';
        if (!file_exists($cacheFile)) {
            return false;
        }
        
        // 检查缓存是否过期
        $mtime = filemtime($cacheFile);
        $lifetime = template_config('cache_lifetime', 3600);
        if (time() - $mtime > $lifetime) {
            unlink($cacheFile);
            return false;
        }
        
        return file_get_contents($cacheFile);
    }
    
    // 存储内容到缓存
    private function storeToCache($key, $content) {
        $cacheFile = template_config('cache_path') . '/' . $key . '.cache';
        return file_put_contents($cacheFile, $content) !== false;
    }
}

// 模板引擎辅助函数
function template($template = null, array $vars = [], $layout = null) {
    $engine = TemplateEngine::getInstance();
    
    if ($template === null) {
        return $engine;
    }
    
    return $engine->render($template, $vars, $layout);
}

// 渲染组件的辅助函数
function component($name, array $vars = []) {
    return TemplateEngine::getInstance()->renderComponent($name, $vars);
}

// 设置当前模板的辅助函数
function set_template($templateName) {
    TemplateEngine::getInstance()->setTemplate($templateName);
}

// 获取全局模板系统配置的辅助函数
function get_template_config($key = null, $default = null) {
    // 使用全局配置函数
    return template_config($key, $default);
}

// 获取当前模板配置的辅助函数（不与全局配置冲突）
function current_template_config($key = null, $default = null) {
    return TemplateEngine::getInstance()->getConfig($key, $default);
}

// 清除模板缓存的辅助函数
function clear_template_cache() {
    return TemplateEngine::getInstance()->clearCache();
}

// 检查是否启用了调试模式
function template_debug() {
    return template_config('debug', false);
}
