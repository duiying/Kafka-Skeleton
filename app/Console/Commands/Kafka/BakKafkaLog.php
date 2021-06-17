<?php

namespace App\Console\Commands\Kafka;

use App\Constant\KafkaConstant;
use App\Util\KafkaUtil;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * 备份数据，只保留当天的日志
 *
 * @package App\Console\Commands\Kafka
 */
class BakKafkaLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:log:bak';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '备份数据，只保留当天的日志';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        KafkaUtil::bakKafkaLog();
    }
}
