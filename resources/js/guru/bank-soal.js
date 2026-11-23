document.addEventListener("DOMContentLoaded", function () {

    const jenisSoal = document.getElementById("jenis_soal");

    if (!jenisSoal) return;

    const pilihanGanda = document.getElementById("pilihan-ganda");
    const benarSalah = document.getElementById("benar-salah");
    const essay = document.getElementById("essay");

    function tampilkanForm() {

        if (pilihanGanda) pilihanGanda.style.display = "none";
        if (benarSalah) benarSalah.style.display = "none";
        if (essay) essay.style.display = "none";

        switch (jenisSoal.value) {

            case "pilihan_ganda":
                if (pilihanGanda) pilihanGanda.style.display = "block";
                break;

            case "benar_salah":
                if (benarSalah) benarSalah.style.display = "block";
                break;

            case "essay":
                if (essay) essay.style.display = "block";
                break;
        }
    }

    tampilkanForm();

    jenisSoal.addEventListener("change", tampilkanForm);

});