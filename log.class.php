<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Baghdad');

class Log {
    // Log Level should be in [ 0, 1, 2, 3, 4, 5, 6, 7]
    private $logLevels = [
        0 => 'All',
        1 => 'Trace',
        2 => 'Debug',
        3 => 'Info',
        4 => 'Warn',
        5 => 'Error',
        6 => 'Fatal',
        7 => 'Off'
    ];
    private $logLevel   = 0;
    private $path       = '';
    private $banner     = '[!]';
    private $dateFormat = 'd-m-Y h:i:s A';

    private $logFormatStr  = "[%LOGTYPE%] [%DATE%] in [%FILELOGGED%] in line [%LINENUMBER%]:\n\t %LOGMESSAGE%.\n\n";
    private $logFormatClient  = "[%LOGTYPE%] [%DATE%] in [%FILELOGGED%] in line [%LINENUMBER%]:\n\t Client [%CLIENT_IP%] with [%USER_AGENT%]:\n\t\t %LOGMESSAGE%.\n\n";

    function __construct(int $level, string $path)
    {
        // Check $level [ Loglevel ] value
        if ( isset($level) && is_int($level) && $level >= 0 && $level <= 7 ):
            // Check $path value
            if ( isset($path) && is_string($path) ):
                $this->logLevel = $level;
                $this->path     = $path;
            else:
                die($this->banner." [".date($this->dateFormat)."] Error in file [".__FILE__."] in line [".__LINE__."]: Not valid log path.");
            endif;
        else:
            die($this->banner." [".date($this->dateFormat)."] Error in file [".__FILE__."] in line [".__LINE__."]: Not valid log level.");
        endif;

        

        if ( isset($path) && is_string($path) ):
            if ( file_exists($path) ):
                if ( !is_writable($path) ):
                    die($this->banner." [".date($this->dateFormat)."] Error in file [".__FILE__."] in line [".__LINE__."]: Permission Denied.");
                endif;
            else:
                $data = "[Info] [".date($this->dateFormat)."]:\n\t Log file created [".$path."].\n\n";
                file_put_contents($path, $data, FILE_APPEND);
                unset($data);
            endif;
        endif;
    }

    private function logFormat(int $logType, string $logBody, string $file, int $line): string
    {
        $res = str_replace('%LOGTYPE%', $this->logLevels[$logType], $this->logFormatStr);
        $res = str_replace('%DATE%', date($this->dateFormat), $res);
        $res = str_replace('%FILELOGGED%', $file, $res);
        $res = str_replace('%LINENUMBER%', $line, $res);
        $res = str_replace('%LOGMESSAGE%', $logBody, $res);

        return $res;
    }

    public function log(int $type, string $body): bool
    {
        $bt     = debug_backtrace();
        $caller = array_shift($bt);

        $file    = $caller['file'];
        $lineNum = $caller['line'];

        if ( $type === 7 ):
            return false;
        elseif ( $type > 7 ):
            die($this->banner." [".date($this->dateFormat)."] Error in file [".__FILE__."] in line [".__LINE__."]: Invalid log level passed.");
            // self::log(5, 'Invalid log level passed ['.$type.']');
            return false;
        elseif ( $type >= $this->logLevel ):
            $logFormatted = self::logFormat($type, $body, $file, $lineNum);
        
            file_put_contents($this->path, $logFormatted, FILE_APPEND);
            
            unset($logFormatted, $bt, $caller, $file, $lineNum);
            return true;
        else:
            return false;
        endif;
    }
}