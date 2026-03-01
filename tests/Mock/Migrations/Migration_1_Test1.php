<?php
declare(strict_types=1);

namespace Tests\Mock\Migrations;

use Fyre\DB\Migration\Migration;

class Migration_1_Test1 extends Migration
{
    public function down(): void
    {
        $this->forge->dropTable('test1');
    }

    public function up(): void
    {
        $this->forge->createTable('test1', [
            'value' => [],
        ]);
    }
}
