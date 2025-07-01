<?php

namespace Vermaysha\PgbouncerLaravelExtension\Tests\Feature;

use Illuminate\Database\PostgresConnection;
use Illuminate\Support\Facades\DB;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use Vermaysha\PgbouncerLaravelExtension\PostgresPGBouncerExtension;
use Vermaysha\PgbouncerLaravelExtension\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    #[Test]
    public function it_uses_default_connection_when_emulate_prepares_is_disabled()
    {
        // Langkah 1: Minta koneksi database. Konfigurasi default dari TestCase
        // tidak mengaktifkan emulate prepares.
        $connection = DB::connection('pgsql_test');

        // Langkah 2: Pastikan koneksi yang dikembalikan adalah koneksi standar Laravel,
        // BUKAN ekstensi kustom kita.
        $this->assertInstanceOf(PostgresConnection::class, $connection);
        $this->assertNotInstanceOf(PostgresPGBouncerExtension::class, $connection);
    }

    #[Test]
    public function it_uses_custom_extension_when_emulate_prepares_is_enabled()
    {
        // Langkah 1: Ubah konfigurasi secara dinamis khusus untuk tes ini,
        // dengan mengaktifkan PDO::ATTR_EMULATE_PREPARES.
        config()->set('database.connections.pgsql_test.options', [
            PDO::ATTR_EMULATE_PREPARES => true,
        ]);

        // Langkah 2: Paksa Laravel untuk "melupakan" instance koneksi yang mungkin sudah ada.
        // Ini penting agar saat koneksi diminta lagi, resolver akan dipanggil
        // dengan menggunakan konfigurasi yang baru.
        DB::purge('pgsql_test');

        // Langkah 3: Minta koneksi database lagi.
        $connection = DB::connection('pgsql_test');

        // Langkah 4: Pastikan koneksi yang dikembalikan adalah ekstensi kustom kita,
        // karena emulate prepares sudah diaktifkan.
        $this->assertInstanceOf(
            PostgresPGBouncerExtension::class,
            $connection,
            'Ekstensi kustom seharusnya digunakan saat emulate prepares aktif.'
        );
    }
}
