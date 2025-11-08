<?php
/**
 * 目录占位文件
 * 防止目录遍历漏洞
 */
header('HTTP/1.1 403 Forbidden');
exit('403 Forbidden');
