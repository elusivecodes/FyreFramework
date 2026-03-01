<?php
declare(strict_types=1);

namespace Tests\Mock\Migrations;

use Fyre\DB\Migration\Migration;

class Migration_2_Test2 extends Migration
{
    public function down(): void
    {
        $this->forge->dropTable('test2');
    }

    public function up(): void
    {
        $this->forge->createTable('test2', [
            'value' => [],
        ]);
    }
}
