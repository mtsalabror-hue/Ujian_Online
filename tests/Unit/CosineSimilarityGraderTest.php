<?php

namespace Tests\Unit;

use App\Services\CosineSimilarityGrader;
use PHPUnit\Framework\TestCase;

class CosineSimilarityGraderTest extends TestCase
{
    private CosineSimilarityGrader $grader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->grader = new CosineSimilarityGrader();
    }

    public function test_jawaban_identik_dinilai_penuh(): void
    {
        $hasil = $this->grader->saranNilai('Budi makan pisang', 'Budi makan pisang', 10);

        $this->assertSame(10.0, $hasil['skor']);
        $this->assertSame(100.0, $hasil['kemiripan']);
        $this->assertSame(3, $hasil['kata_cocok']);
    }

    public function test_contoh_soal_ujian_nilai_66_67(): void
    {
        // Kasus dari halaman tinjau jawaban: kunci "yang makan pisang
        // adalah budi", jawaban "pisang dimakan oleh budi", bobot 100.
        $hasil = $this->grader->saranNilai('yang makan pisang adalah budi', 'pisang dimakan oleh budi', 100);

        $this->assertSame(66.67, $hasil['skor']);
        $this->assertSame(66.7, $hasil['kemiripan']);
        $this->assertSame(2, $hasil['kata_cocok']);
    }

    public function test_kata_negasi_tidak_dibuang(): void
    {
        // 'tidak' sengaja bukan stopword: 'tidak merusak' tidak boleh
        // dinilai identik dengan kunci 'merusak'.
        $hasil = $this->grader->saranNilai('merusak', 'tidak merusak', 10);

        $this->assertSame(70.7, $hasil['kemiripan']);
        $this->assertSame(7.07, $hasil['skor']);
        $this->assertSame(1, $hasil['kata_cocok']);
        $this->assertLessThan(100.0, $hasil['kemiripan']);
    }

    public function test_jawaban_kosong_dinilai_nol(): void
    {
        $hasil = $this->grader->saranNilai('Budi makan pisang', '', 10);

        $this->assertSame(0.0, $hasil['skor']);
        $this->assertSame(0.0, $hasil['kemiripan']);
        $this->assertSame(0, $hasil['kata_cocok']);
    }
}
