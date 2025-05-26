<?php

// 严格模式
declare(strict_types=1);

$version = "v0.0.1"; // 版本号

// 当前文件修改时间
$fileTime = filemtime(__FILE__);

// 获取当前系统
$system = strtoupper(substr(PHP_OS, 0, 3));

// 获取命令窗口执行的路径
$cwdPath = getcwd();

// php-cli后缀
$suffix = "";

// 判断当前系统
if ($system === 'WIN') {
    // Windows系统
    $suffix = ".exe";
}

/**
 * 输出带颜色的命令行消息（符合PSR-12标准）
 *
 * @param string $msg 消息内容
 * @param string $type 消息类型，可选值：error, success, info, warning, default
 * @param boolean $isTitle 是否显示标题
 * @param boolean $useBuffer 是否使用缓冲区
 * @return void
 */
function outputMsg(
    string $msg = "",
    string $type = "default",
    bool $isTitle = true,
    bool $isLine = true,
    bool $useBuffer = false
): void {
    $colors = [
        'error' => ["\033[31m", '错误：'],
        'success' => ["\033[32m", '成功：'],
        'info' => ["\033[34m", '提示：'],
        'warning' => ["\033[33m", '警告：'],
        'default' => ["\033[37m", ''] // 重置颜色为默认
    ];

    $config = $colors[$type] ?? $colors['info'];

    if ($useBuffer) ob_start();

    $line = $isLine ? "\n" : "";

    $res = sprintf(
        "%s%s%s%s$line",
        $isTitle ? $config[1] : "",
        $config[0],
        $msg,
        "\033[0m"
    );

    $isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    $output = $isWindows
        ? iconv("UTF-8", "GBK//IGNORE", $res)
        : $res;
    echo $output;

    if ($useBuffer) ob_end_flush();
}

// 函数：从命令行参数中解析选项  
function parseArgs($argv = []): array
{
    $options = [];
    foreach ($argv as $index => $arg) {
        if ($index === 0) {
            // 跳过脚本名  
            continue;
        }
        $options[] = explode("=", $arg);
    }
    return $options;
}

// 运行PHP文件
function run(string $file): void
{
    // 执行命令行命令来运行PHP文件
    // 由于 $suffix 是全局变量，在函数内使用需要先声明
    global $suffix;
    $command = __DIR__ . DIRECTORY_SEPARATOR . "php{$suffix} {$file}";
    exec($command, $output, $returnVar);
    // 检查命令执行结果
    if ($returnVar !== 0) {
        // 命令执行出错
        outputMsg(type: "error", msg: "运行PHP文件失败。");
        die;
    } else {
        // 持续输出结果
        foreach ($output as $line) {
            echo $line . "\n";
        }
    }
}

// 编译单个可执行文件
class Compile
{
    private string $mainFile = ""; // 主文件
    private string $outFile = ""; // 可执行文件名称
    private bool $isWin32 = false; // 是否为win32程序

    public function __construct(private array $args)
    {
        global $system;
        // 构造函数
        $this->main();
        $this->out();
        if ($system === 'WIN') {
            $this->win32();
        }
        $this->winCompile();
    }

    private function getEvbDir(string $fileDir): string
    {
        // 根据文件路径获取项目路径的文件夹
        $absPath = dirname($fileDir);
        $handle  = opendir($absPath);
        if (!$handle) outputMsg(type: "error", msg: "获取项目路径异常。");
        $xml = "";
        while (false !== ($entry = readdir($handle))) {
            if ($entry === '.' || $entry === '..') continue;
            $path = dirname($fileDir) . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($path)) {
                // 获取文件夹名称
                $dirName = basename($path);
                $child = $this->getEvbDir($path); // 递归处理子目录
                $xml .= "<File>";
                $xml .= "<Type>3</Type>";
                $xml .= "<Name>{$dirName}</Name>";
                $xml .= "<Action>0</Action>";
                $xml .= "<OverwriteDateTime>False</OverwriteDateTime>";
                $xml .= "<OverwriteAttributes>False</OverwriteAttributes>";
                $xml .= "<HideFromDialogs>0</HideFromDialogs>";
                $xml .= "<Files>";
                $xml .= $child;
                $xml .= "</Files>";
                $xml .= "</File>";
            } else {
                // 获取文件后缀名
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if ($ext === 'exe' || $ext === 'evb') {
                    // 跳过exe文件
                    continue;
                }
                // 获取文件名
                $fileName = basename($path);
                $xml .= "<File>";
                $xml .= "<Type>2</Type>";
                $xml .= "<Name>{$fileName}</Name>";
                $xml .= "<File>{$path}</File>";
                $xml .= "<ActiveX>False</ActiveX>";
                $xml .= "<ActiveXInstall>False</ActiveXInstall>";
                $xml .= "<Action>0</Action>";
                $xml .= "<OverwriteDateTime>False</OverwriteDateTime>";
                $xml .= "<OverwriteAttributes>False</OverwriteAttributes>";
                $xml .= "<PassCommandLine>False</PassCommandLine>";
                $xml .= "<HideFromDialogs>0</HideFromDialogs>";
                $xml .= "</File>";
            }
        }
        closedir($handle);
        return $xml;
    }

    /**
     * 主文件
     * 命令：--main=XXX.php
     *
     * @return void
     */
    private function main(): void
    {
        $isMain = false;
        foreach ($this->args as $index => $arg) {
            if ($index === 0) {
                // 跳过脚本名
                continue;
            }
            if ($arg[0] === "--main") {
                $isMain = true;
                $this->mainFile = isset($arg[1]) ? realpath($arg[1]) : "";
            }
        }
        if (!$isMain) {
            outputMsg(type: "error", msg: "缺少`--main`参数。");
            die;
        }
        if ($this->mainFile === "") {
            outputMsg(type: "error", msg: "`--main`参数缺少文件名。");
            die;
        }
        if ($this->mainFile === false) {
            outputMsg(type: "error", msg: "`--main`参数文件不存在。");
            die;
        }
    }

    /**
     * win32程序
     * 命令：--win32
     *
     * @return void
     */
    public function win32(): void
    {
        foreach ($this->args as $index => $arg) {
            if ($index === 0) {
                // 跳过脚本名
                continue;
            }
            if ($arg[0] === "--win32") {
                $this->isWin32 = true;
            }
        }
    }

    /**
     * 可执行文件名称
     *
     * @return void
     */
    public function out(): void
    {
        $dir = dirname($this->mainFile);
        $this->outFile = $dir . DIRECTORY_SEPARATOR . "outFile.exe";
    }

    /**
     * 编译
     *
     * @return void
     */
    public function winCompile(): void
    {
        global $suffix;
        global $cwdPath;
        $sfx = "micro.sfx";
        if ($this->isWin32) {
            $sfx = "micro-win32.sfx";
        }
        $sfxPath = __DIR__ . DIRECTORY_SEPARATOR . $sfx;
        $evbExe = dirname($this->mainFile) . DIRECTORY_SEPARATOR . "build.evb.exe";
        $command = "copy /b \"{$sfxPath}\" + \"{$this->mainFile}\" \"{$evbExe}\"";
        exec($command, $output, $returnVar);
        // 检查命令执行结果
        if ($returnVar !== 0) {
            // 命令执行出错
            outputMsg(type: "error", msg: "编译失败，请联系作者。");
            die;
        }
        // 创建evb文件
        $evbFile = dirname($this->mainFile) . DIRECTORY_SEPARATOR . "build.evb";
        $projectContent = $this->getEvbDir($this->mainFile);
        $evbContent = <<<EOD
<?xml version="1.0" encoding="windows-1252"?>
<>
  <InputFile>{$evbExe}</InputFile>
  <OutputFile>{$this->outFile}</OutputFile>
  <Files>
    <Enabled>True</Enabled>
    <DeleteExtractedOnExit>True</DeleteExtractedOnExit>
    <CompressFiles>True</CompressFiles>
    <Files>
      <File>
        <Type>3</Type>
        <Name>%DEFAULT FOLDER%</Name>
        <Action>0</Action>
        <OverwriteDateTime>False</OverwriteDateTime>
        <OverwriteAttributes>False</OverwriteAttributes>
        <HideFromDialogs>0</HideFromDialogs>
        <Files>
        $projectContent
        </Files>
      </File>
    </Files>
  </Files>
  <Registries>
    <Enabled>False</Enabled>
    <Registries>
      <Registry>
        <Type>1</Type>
        <Virtual>True</Virtual>
        <Name>Classes</Name>
        <ValueType>0</ValueType>
        <Value/>
        <Registries/>
      </Registry>
      <Registry>
        <Type>1</Type>
        <Virtual>True</Virtual>
        <Name>User</Name>
        <ValueType>0</ValueType>
        <Value/>
        <Registries/>
      </Registry>
      <Registry>
        <Type>1</Type>
        <Virtual>True</Virtual>
        <Name>Machine</Name>
        <ValueType>0</ValueType>
        <Value/>
        <Registries/>
      </Registry>
      <Registry>
        <Type>1</Type>
        <Virtual>True</Virtual>
        <Name>Users</Name>
        <ValueType>0</ValueType>
        <Value/>
        <Registries/>
      </Registry>
      <Registry>
        <Type>1</Type>
        <Virtual>True</Virtual>
        <Name>Config</Name>
        <ValueType>0</ValueType>
        <Value/>
        <Registries/>
      </Registry>
    </Registries>
  </Registries>
  <Packaging>
    <Enabled>False</Enabled>
  </Packaging>
  <Options>
    <ShareVirtualSystem>False</ShareVirtualSystem>
    <MapExecutableWithTemporaryFile>True</MapExecutableWithTemporaryFile>
    <TemporaryFileMask/>
    <AllowRunningOfVirtualExeFiles>True</AllowRunningOfVirtualExeFiles>
    <ProcessesOfAnyPlatforms>False</ProcessesOfAnyPlatforms>
  </Options>
  <Storage>
    <Files>
      <Enabled>False</Enabled>
      <Folder>%DEFAULT FOLDER%\</Folder>
      <RandomFileNames>False</RandomFileNames>
      <EncryptContent>False</EncryptContent>
    </Files>
  </Storage>
</>
EOD;
        $is_file = file_put_contents($evbFile, $evbContent);
        if ($is_file === false) {
            outputMsg(type: "error", msg: "创建evb文件失败,请联系作者。");
            die;
        }
        // 执行evb文件
        $evb_exe = dirname(__FILE__) . DIRECTORY_SEPARATOR . "enigmavbconsole.exe";
        $command = "{$evb_exe} {$evbFile}";
        exec($command, $output, $returnVar);
        // 检查命令执行结果
        if ($returnVar !== 0) {
            // 命令执行出错
            outputMsg(type: "error", msg: "编译失败，请联系作者。");
            die;
        }
    }
}

// 解析命令行参数
$command = parseArgs($argv);

// var_dump($command);

function help(): void
{
    global $suffix;
    global $version;
    global $fileTime;
    outputMsg(<<<EOD
  ___           _                         _             
 |_ _|  _ __   | |_    __ _       _ __   | |__    _ __  
  | |  | '_ \  | __|  / _` |     | '_ \  | '_ \  | '_ \ 
  | |  | | | | | |_  | (_| |  _  | |_) | | | | | | |_) |
 |___| |_| |_|  \__|  \__, | (_) | .__/  |_| |_| | .__/ 
                      |___/      |_|             |_|    
EOD);
    outputMsg(msg: "intg{$suffix} ", type: "success", isTitle: false, isLine: false);
    outputMsg(msg: "version ", isTitle: false, isLine: false);
    outputMsg(msg: "$version ", type: "info", isTitle: false, isLine: false);
    outputMsg(msg: date("Y-m-d H:i:s", $fileTime) . "\n", isTitle: false);

    outputMsg("是一个集成工具，用于编译和运行PHP文件。");
    outputMsg(msg: "用法：", type: "warning", isTitle: false);
    outputMsg(msg: "  intg{$suffix} run <文件名>", isTitle: false);
    outputMsg(msg: "  intg{$suffix} compile --main=<文件名> <--win32>", isTitle: false);
    outputMsg(msg: "帮助：", type: "warning", isTitle: false);
    outputMsg(msg: "  intg{$suffix} help", isTitle: false);
}

if ($command !== [] && !isset($command[0][0])) {
    help();
    die;
}

// 执行命令
switch ($command[0][0]) {
    case "run": // 运行PHP文件
        if (!isset($command[1][0])) {
            outputMsg(type: "error", msg: "`run`命令缺少参数。");
            outputMsg(type: "info", msg: "例如： intg{$suffix} run demo.php");
            die;
        }
        run($command[1][0]);
        break;
    case "compile": // 编译可执行文件
        new Compile($command);
        break;
    default: // 显示帮助信息
        help();
}
die;
