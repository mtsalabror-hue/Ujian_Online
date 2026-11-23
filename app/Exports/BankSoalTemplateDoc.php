<?php

namespace App\Exports;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Membuat template bank soal (.docx) — format rapi sesuai template
 * kiriman guru: judul RATA TENGAH, lalu KELAS & MATA PELAJARAN, lalu
 * tiga bagian soal. Contoh PG menyertakan GAMBAR (menunjukkan bahwa
 * soal boleh memakai gambar; parser menempelkan gambar ke soal).
 * Soal juga boleh memakai TABEL — parser mengubah tabel jadi HTML
 * <table> yang ikut terimport sebagai bagian pertanyaan.
 *
 * Contoh soal TIDAK ikut terimport ke bank soal — parser melewati soal
 * yang baris pertanyaannya diawali "CONTOH:" (lihat BankSoalDocParser).
 * Guru menghapus/mengganti baris contoh dengan soal sendiri sebelum import.
 *
 * Struktur:
 *  TEMPLATE BANK SOAL (rata tengah)
 *  KELAS: VII
 *  MATA PELAJARAN: Matematika
 *  A. PILIHAN GANDA        -> pertanyaan (+gambar), opsi A-D, lalu "Jawaban: X | Bobot: N"
 *  B. BENAR / SALAH        -> pertanyaan, lalu "Jawaban: Benar/Salah | Bobot: N"
 *  C. ESSAY                -> pertanyaan (+tabel), lalu "Jawaban: kunci" dan "Bobot: N"
 */
class BankSoalTemplateDoc
{
    /**
     * Gambar contoh (kelas/meja) tertanam sebagai base64 — dipakai untuk
     * membuat file public/images/contoh-gambar-kelas.jpg bila belum ada di
     * server, supaya template unduhan selalu bergambar. File biner tidak
     * bisa di-push lewat konektor GitHub, jadi generator membuatnya sendiri.
     * Gambar asli (425x225) dikecilkan ke 340x180 agar string tidak terlalu besar.
     */
    private const CONTOH_GAMBAR_BASE64 = 
        '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAwICQsJCAwLCgsODQwOEh4UEhEREiUbHBYeLCcuLisnKyoxN0Y7MTRCNCorPVM+QkhK' .
        'Tk9OLztWXFVMW0ZNTkv/2wBDAQ0ODhIQEiQUFCRLMisyS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tL' .
        'S0tLS0tLS0v/wAARCAC0AVQDASIAAhEBAxEB/8QAGwAAAQUBAQAAAAAAAAAAAAAABQABAwQGAgf/xABVEAACAQMCAgUFCQwEDAYD' .
        'AAABAgMABBEFIRIxBhNBUWEUInGBkRUjMkKhscHR4QcWJDNSVWJydJKTlGOistIlNDVDREVTVmRzgvAXVIOEs/EmJ8L/xAAaAQAD' .
        'AQEBAQAAAAAAAAAAAAAAAQIDBAUG/8QALhEAAgIBAwIFAwQDAQEAAAAAAAECEQMSITEEURMUIkGhM4GxMmGR8AUj4XHR/9oADAMB' .
        'AAIRAxEAPwDq7upNMuHtLJhFcwgeVXnCGdXYD3uPbAwOZ7cVZs7W1vUz98WovITgh7gxkk+H1UInK+V3sk5JZ7+flns4QKp6o1uI' .
        'UKh48NlnxywD3HNdcpLD6IbUKlijpitkbA9HCpw+q6quOebptqmTowrjI1vU8ftLUAaS+0NEiivpEuCQzWxlE8ZyCR5zAEAgZO+w' .
        'IovpPSOaVUTUbZEkdiPKIJlaAkjYFs+afA1PmZk+IyWXoxJDuNT1WSP4xS6YkD0HnUkHRSO7Tih6Q6mRn/btkekZozFNLhWEex5H' .
        'rV3+WlLbNM/WCAxzDlLFKqn1ijzGT2fwitTBbdB5QPN13U2/WuGFR/eVd9mq3v8APSfVR2O+vLNPw62Lp/tYsH2jsojbXCXEYdCC' .
        'DywwPzUvM5f6kPUZNOhFwT75rOoL4rdOfnrsdBnH+v8AVc/88/XWvpUPqcr9/hDtnn+tdHbrSoWljvtTukGDkXrJ6c7Hwx30O0qC' .
        'LVI3KajqsV0hPHbtesWA/KXbfHaK9PdA6lWAIIwQawvSXovLZze6WlcS9XhiEPnpju7x9G24oXUZO5Lky1B0QW7iWWHX9UKHl7+d' .
        'vDnXf3it+f8AVf47fXUXR3XfKJC0cbLOF4p4FG0n6aDv7x41sIpesRXVDwsMjJHKh9RlXv8ACGpNmU+8V/z/AKp/HP10vvFf8/6r' .
        '/HP11rxSpeZy9/hDtmQ+8V/z/qv8c/XS+8V/z/qv8c/XWvpUeZy9/hBbMRqHRM6faNO2t6rIAygjyhhgE476YdF+LWHsPdnVQy24' .
        'm6zyk7gsVxj1fLWr1uIzaVcoOfASPVv9FD7djJ0rWXsfTFO//M+2jzGTv8IWp2DT0GfG2v6r/HNRt0JuQfN1e/Yd5vXH0Vs6VPzO' .
        'X+pDtmLHQq85e619j9uf+7Uo6EysgD65qSEfk3DGtfQjpNqUun6fwWa8d/dN1Nqn6ZHP0AZPqo8zk7/AWzKWOiG/1G9tbfU9Ua3s' .
        'yI2ufKmw0vaoHcO/NEZOh/URNLJrmphVGT+ENvRnQ7CPSNPgsoYpeGNfOcru7HdmPiTVni8slVjFJ1MZyuV+Ee+jzGTv+CdTM7B0' .
        'NkmiV21nVI878PlDH6a7PQZif8var/Hb661XW4+I/wC7S67+jk/dpeYyd/hFWzK/eM35+1X+O310h0HYH/L2qfx2+utT139HJ+7X' .
        'LXGP81MfQlHmcvf4QajMnoS3591T+O311yehjD/Xmp/zDfXWla6ABPVTYHbw0C1DpEHhdtPMQiXIe9uDwwRnuHa58Bt40/MZO/wi' .
        'NbBuodHrbTbcz3nSHUo0zgfhD5Y9wG+T4YoS9pFZoZtT1TVrKBtoFku2a4lzy97A831nNXrCDUdXuBc6VGeNhwtq9+m+P6GPsH/e' .
        'a0uidFrLSZPKWL3l+2S93cHick88fk+qjzGTv8IalIzek9G7/UwZZZtWsbf4hmvWMj+JQDb2+qiP3jyHc6/qmfCc/XWvAxSpeZyd' .
        '/hFWzFS9Db1M8Gp30q/tzKfmx8tQDonqHbPqXq1Gtlc38NsDxcTkcwi5x6e6hkmtPcKfJIZmX9CM59p2Hy0/M5f6hOX7gE9FLxTx' .
        'S6hfQr+lqLb+gAU02gwwgF9d1MMRnAuWJ9Q5mi5WWVi1xLKudisUZLfvkZHqFTwCCFuG3gcOeZEZz6zT8xk5/wDhOpgCLo1LIwPu' .
        'lqscXY0l2wJ9CgfOa7udH1XT4Hl03VprzAy1ne4kSVe0ZNayOydiC7BfAbmrEdrFHghckdppeYk+d/4Hcnyef21g2r28d5pCqkEg' .
        '86KRwDE/xlGewfTSqPo5I0MeoRqSFW+lAA7NxSrWfQw1PTwZeQwS3aBVzvLdft1z86UN1VQ9ljAyXAB7tjRK5/G3P7dc/OlUb6Np' .
        'bMogyzOFXwPCax6n6j+34N5j9IXkkt7lnOeK2tCSRnJ6nJ+eitvYeU+4drZ2lsZptN69lbzVkIPh8YY2JrjpZaCHopp13F5xuxAB' .
        '/BVcfPU/SeFtKvtES1V5Bb2zxKEkCMQGbke0+FYGHuOr3eiScAM+mnkYrgccDehqM2nSgwqDfxNEG366Hz4j7OVCrTphLCfJ7yQS' .
        'LjHUX8XC3qbt+WrPDod0SyPcaNO/Mr58LHxG4x7KouzVWurRXESyRcciNyeNeIfPTl4+IyW4mhkJySsZKn0isVd6RqGmo97atHJE' .
        'BkXVg+B/1Jy9lc2/TCWCMG7hWfJ4Q8LhD6SDSEbtNYMQAvImTfHWKpKn6qtSapaxIjzO0aPsrMpAPrrK2fSC1vJmt0k4JwvEYZ/M' .
        'bHr2PqqbIRXELxIrDzojJlG9Qp0hpmmj1KykGUu4G7Pxgp3v7RBk3MWPBgazFveQwzhLqyhdQAMqw4gO8EbN68GtFYmxuoestGV0' .
        'B4Twk7HuI7DSpD3Mvqmg211qMV5pMstq4lWRykRK885XOMZ7ezejjamtpOmOLExY9TnLHHMr9VFfJ4ufDnHeSazfSrou+oYvtKfq' .
        'dRhHmAsQjgch+ifEeunaJqRoIr+GVFePjZGGQQpxXQu4z2P+4awGi9Im66S3uIngvIj79bvsSe1l8e8DbtHOtEt4SoYYIO4PEKKK' .
        'th8XKHsf9005nUdjfumgPljA8v6wp/LG/J/rCikK2GZpkeJ14XOVIxwmgliy+6EOpGR+DybydohHnhPECDmu1vHyDw5H6wrO6RM6' .
        '9O4LRiepktJMxk5BwdtqKQO3wbpL62bHvqqT2N5p+WpPKIf9qn7wqFtPtzyRl/VYiufc2DOffP3qWweole8t0xmZDnsBzQcBZdfb' .
        'UbhiywxdVax9Wfe+Ld25bk4A9AqLpMyWsMNlZjhvL8mMOST1cY3kf1D5SKia+W0jjihQlgOGJS4OMdpNNJBcgtc30c0yWqMw4hxS' .
        '4Ukhe6ra3UCqApYAbAdW31UCs3NujBiXlc8Uj8fwj9VWfKzjYf1hRQ7YV8sh73/ht9VMb6Ac2b+G31UHa4c9n9YVDLcLDG0szpHG' .
        'oyzu4AWikFsNnUbf8pv3TVafWLWO4itVxJdTfAhGzHvJ7h41g7zplcajdDTujFvLcXDc5wueEd4B5ek0Y6PdB5UkW91y7lluieLq' .
        '45Tsf0n5n0DAo2QbsK6xZXuo2DJ7wsvECsLE9UR+ljdvkqCw6OWCzRzavcC/uU/FrKvDFF4KnL25rRmzgOMJw45cJI+aqt1b20K5' .
        'muXRT2F+foo2IqaZeWSMjKupHeDXEt5BCpaSZAB41nJLmyLYtrR7kg/DYgKKgKvku6x24P8AswAcemikVbDs+vWUQAVy7nkvCRn2' .
        'iqbavPcbRKsCn40mc+pR9dDYpo0LG3jZnx8Ibk/9VTobmU44+BTsQvM0tkO7Jw9p8K4kMxUA5lOEHjw8hQ2Xpzo4ulgjmuJRnHHF' .
        'ASo9Az8wqfV7NJNNMDO6tcPgcRyGI7Dneg1jLbwwwwyW8bjrTF1qxluLwPhWyj/qeQwll0y0h/T9ctrpn4mXqZWZbaVWyCQMlGHN' .
        'X2Jweyr3Rq9S8jm4ew9vdWRkezae5tbNSjSQdZKgGyuhBU57d9v+o1ouhdu0SXL/ABchQPEc65t06ZtB6lZp6alSqizzPQuepft8' .
        '3zilTaGd9S/b5vnFKvelydC4B1z+Nuv264+dahRQ0toG5C7jJ9AzmrTQGea7A5i+uD8q1DHGWuLaNdy0wC+nBx8teT1X1H9vwc+T' .
        '3NC1qD0c0Q3g9607q5ZgeYwGGPbj2UrS2mvbjo62owpNI1nMrrKgZS2MjPqNc67ex3/Rm6itTk3Fuipj8vzTj04Jo5dzwW+r6dbK' .
        '/D5PxBiPi5ibGR6hXO+Dji292UbPo7p48nU2wdWIbBLcBwPhAEkDJ32rOz6DMOvvLS4WGJnYsqZDKAx+Jup5dmDW+s7ZYo4FTh4V' .
        'ZscJJGMHageAnUWxkWe4ku3Matt1MeSW3x2DO2/OtYVSspt2YvU5NR0J2juIDKkqlC9vlQ3pG4PoqvpZvF1O0N3B1bRSxyxcQHEy' .
        'luEj2Vu5rVobeFXlWaUL57KuAxxucUD1teDXNL4VBHUKSPRMMn1CnkilwOMrQL0VS9vIJXkZlk4VVmJUDhydu/iyc0R6lTjIFWja' .
        'pAsgChXOoXEhH6LEhT/VNILuNqwZrj4GjgG2w9lT2/4JcpNBIYrgbDqwSX8CBz9Bp5n8ntJZVQsyLsB2nsGaOdH9NjeC4mML25lX' .
        'geVZAwnXBGQeYAzt6BTS23Kt+w9v0huetEMqws+cHiVoyfDtGavNq1yR5tqgbxkP1VLc6fBPacHD14weDcZ3Ocg+FU9LRnWa2lPF' .
        'LaycBY7kqRlT7M0n+w9yhe2Meo38N9dW8JuogVjkVSCo8d9/Duq1Dp/LaiotuW1Wo4gopWxg2LTgRyFSLpq5zgVX1/V7nS5bdYII' .
        '5VkVixdiMYxjGPTQ49KL3hz5JBn/AJh+qnQrQcGmpjcA9mKodIEstHtn19rXrrnT4WCHj4SVOxGfX3UK++7UePHkVtjv6xvqpS9I' .
        '9RnjZGsbR4yPOV8kfbTSC0aa71S2tJ7SCV/fbtykQG+SOdD4ektpK0Y4HQPJcIeLn7z8IjFZe76XajbOrtpenO0eSpyQRnc4PjgV' .
        'Ubp9qEcmPcrTQVLfGbYMdzy7cGigs002odH7prbV5ml47xls4mYEEHOQAOzc5zV24t7S11Kxsxb5F11g4uPHDwjPLt+isYnTy+PB' .
        'nSNNPCcrgtse8bU7fdC1PaWTTLDiQkI5Z9iQNgfXvimBv5dPiXhEa9/Peohp6c+EDxrDf+IutyBzBpNrJ1adY+C/mJ+UfCuYful6' .
        'tgtNptmVxsFkdT8uaQG58hTJwuRUF5o9tf2cttdQrJBKPOU+G4IIrHn7p94McWkQ8wNpz2keFaS86Qar7qX9pp+lW9wliU43kuur' .
        'PnDIOCKcYuXAm0glpdnDpEHk9jZQQxDfEfm59J3yauNdyAbQZP69ZuTpJrscZd9EswoGT+Hjb1cNRjpNr7F+Ho9Ayx/DYXmynfY+' .
        'bz2+UVp4E/6xa4v3D11NeSqVSRYR3pu3tNBH0iLjaRiXdvhO5yT7aik6S66q8cnR+3RD8FmvQA3M7bdwz41E3SLWSSG0G2Ddqm9w' .
        'R7RT8vN8V/IOcSwdOjXbArmOzRJR5tD7jpJqVv1TXOiwRxvKsZcXXFgk45Yo9qD29g/FPII1yQMgnNZZMbxq5DjLVwKKIDGBirkM' .
        'OSPGgs3Sawt1Bigu7k8hwR8I9pqbRukV1qWrJbR6WIoOr45JGmy6DOBsNuYrFziXodBjU9JS/seAv1MkZ6yOXGeFu/2VjbnROk0a' .
        'rDaQRPCTxdYk4Xi9u4r0UvhfrrK65p+h3l+bnWNTjDRjzYvKAgUDwBzVx6yWOOiO6IeGMnbQ/Rvos+nxyyXsizXcpHWuFwgUHIRe' .
        '3GcEntxR7RUSOy805y7E+2qVtrVmIY47Nb27QDzSkDtt+swGfbT+X38sf4NokuM7C4lSMewZPyVlLPOctTVstRSVIMl1HbTdaucZ' .
        'oOU12YNmTT7MEbYR5mHtKj5KF3dxBAOHUul/VupwywmKL5ACalZJtlUgDopHWanv/p8vb4ilXekluu1TO34fLsD6KVfTM0RR6wxz' .
        'XhB/064HyrUUbiO6sHHNbqM/LTzfjLv9uuPnWoo8eV2fF8EXCZ9uPpryup+q/t+DDI6TDFppRtIdMtSd31MyHH5HB/8AVHfc94ek' .
        '1xfEs3WoSqg/qjHyGqUFyBf6S8mygurekogBo6pSa0aW4kKpbuRK245d3tFc7Vo4fU2i7ZxlY4ld+Jgx3K8J9lV4LXMQ6wAhXcpg' .
        '8gSc1LaX1vcOkcLN34YEHlXdrcQhChkUMrNkes1a2NmrKN3BsPN796z+rwrF0g0maXAhSBw5PLBatZezW4RS88a77ZYb0D1u1S51' .
        'Owhf4EkRUkd3EKJyslRpAe7uBNJCybi5uJSpxyWNAuP3iTXSJ5wq9NZKNPjnUDK3U/D4BnOf7NRRR7jvrNmuNJRpCntZLixlSEe/' .
        'Yym3Mjs3qzouk6bqFs5uEkmtpZGSK3Er9XAq7lGQYAOc+narVtHjFWhpyNN18MklvNjzmjxhvSCMHlTT2pl07sltTDpNl5JZR8ME' .
        'EfWQlj5hQnkG7+eM+FN0c4pxd33+bupcxbEZRRgHfsO5HhXMWgRMyi7uZ7iJMcMTEKm3YQOfjk79uaNgAchR/wCDEBT0qVAzM9Lh' .
        'm4sv1ZM/1aBScsZyaOdLyBPZZ/Ik/wD5rOu5VTjmeVUjN8jMqqS3H8EZI7cd/h21IrOZ/Jo8SSHcBCCOXFueXwd/Cp7W7Fpossiy' .
        'cM80kkcSfl7AcTeCk/KKge+eO1WPTYBAYjxhmjXeNsR44QNyTjLHke/cUDoeHSzqJtRLMkCzxmQEAsyqMYztgEggjNO+i2UMem22' .
        'p30MYWKaQjiChi7L5ud9lPtye41FHBftYmG+hu2eKeOOBGfh5hi/gQMc/Ziqj2C2tpp91cSRBL6MlR1wQR5XK+dzYcsjG3LtoGZ+' .
        'SN4HAcZYHhYg5GRsQPWKtx6hMtjbCKzPDZu/vikedNIPNOMbkAE49FRTSGTgL74G+MmpLN7k9Tb28mA06yKrnADqcht+XaT6KAsm' .
        'ul1fyjhmto/KtTkRVJI4y/Dw5ODtkE57twMYqhNYXEUMkksRCQzNA5yGKkHGT2gdgOKMtp88aXKzaqyPDLOYcec0xVMsQfijBHhv' .
        'XJsrgTeT3k0qteWp1CQRoodyOI8Dt6s8+2kBnTEJGjXIAMiAgHPxhXoucdLNeBXjBNv5uSAfMbn31hVhAaHGcGSIjJyQCy7Vumx9' .
        '9Wvkg/6P/Zaunp1u/wC+5h1EqxtotomAjDgRkm6wMo2wRsOecjlTTJNHE/UMffMB4wgK4AIBycnPLfmcCrVrG5TY5CjfekJ0HGUJ' .
        'wPNGMEGqyutkrPPWSYPfT5ZFTiKnDxOS6gGQp8EMRuQOyopyJLi6E5LXc44FaVAAAN2ZfQvDg94onOHPVOCGUciDzNVb+S1uIMXQ' .
        '4l3DZ24R2nI3rj1z1brZmkZzfIB6QxnqLMlcA3sA5bfCrT9LI/MB4QSscrjPguaEdIdKSysLcmd5g2p2xTjbPNhkDwrRdKEJtZD2' .
        'C3mP9WpyfSR6PTx07MwMFu1wAojklyOQU7Y9FFtEs5rXXrWCPjthcRlZVTIYqASDvuN6PaREh0W1LKM5Y/1ahtEA6T2eAABE39mu' .
        'fRSTOlzbtA7V7/T9PupIDpcuoSROVLXdySpOxzhsjt7qot0s1C3ZV0zRtKtA3NSxP9kLRK8sZNQvNbht0V7s3KFATgBcDJz30Nv+' .
        'jOo2VrJc3DQCJBlgGJPzVviw6lyaqEGrbouaVJ0z1+zF5HqmnWdvIzDAhJZMHHaPppNpc7SPFqXT5+5o4THCR68mq3R3pJHolvJa' .
        '3EFxKssg4AmOFM7Ht233qjrUS+698VUKDKcDu2Aro8vKMqmgwYFmk0nwEx0U6KS5bUOkEt+ewz6gpx7KL6f0C6LpFxR6ek6N5yyP' .
        'KX4h4HNYGSFCpBRSMHYivUOjDu2m24YjhFtFjw80VM4aaofUdP4KTsxujsXl1NiASb+X5xSpaKp49TyN/L5vnpV7jJXBQm/G3f7d' .
        'cfOtVJ36uW3AwffAw9Rz9FWpfxl5+3XHzrVqz0g3TxPcKydajizw+OsmwcK3cMZPqryOp+q/t+Dly8MuavZ3EWn2rwtmd58wAeKq' .
        'gPtatHfiOCOy0ONh1siFi7jI25k+JJzU1t0dibT4Ir89bLHAYsqSAM43Hj5owawVvqE80lpd3WLl4ourPFszpgjc9p3zXPpfKMd1' .
        'BdzYPaTWhifromydihOxp2uOrEkuTIybvwgjft3O1TaOiXejxMrKwLsy8IxjO+CO+hqTI07KzvlTw9X8U+mss+WUIJoznJpWi+jw' .
        'anAweE5Chhk8q5t0nlvbXyh1d0DcJVcYGRgU2mKvXzhGPCYxhc7Dc1NE3V38IJ/7zSxycoWy8ctUdzhEKWrQSYz5XKF8QSWrg22D' .
        'mp2Be/t4yMcMkxPs+2rphA7KuzaK2KtumCKI24ANV+DhPKrEDY2oRReHKnoTe69bWN7bWcqTNLcIXXq04gANtz66ty36Qxl3jmAH' .
        '6FWKy1mnrJar0uSTRdam00SxXOnRK3FLHtliQMDt5GtVES0aE8yBmgZlem78Nzp470l+dKzxbIwe2i/3RJOC90sDmUm+eOgUR2yT' .
        'zpoh8nExIywBJPMVPFqbRaSEt5VSWFIzgruz9czMvivDwnHbXErqBjtNVZQrIdvR3jxpiLI1O8nMS3d510jS9ZFFaxgSEnPCvH8V' .
        'QGOFGSM99ReXS6VxR2gijukBU9WOIQ968XORu/JAHd3VvwuFUgsYZZb+6XjJiXiMEJyBgjkz8yTyGPTVMi3sfMLpdXK7cCNmCHuy' .
        'R8Mnw28TQUhRjzesYll3Ifv7/TUsc1j1V01zIePyd0gQA8PGQdyRywNvXVC4nlkf36Rnbs5bejG1d2cTT3EMUUAnlkcBImJwzdgO' .
        'PWT6KAoKRz9HzLaxN1kUPWBJyrMXlXgGSRzVeJjk9vDS0+60xooUNhc3V+yK/WkljK2G4th8Xi4QfyhV8jWrK71bUbe0g65JAs9w' .
        'z+aqxp5wXi33JB9lVrcancQwWkNsYWk0wxw9U5Tij4xws2+diTy387wpDB17dRTQ6TFHGRJFIommZeHjLODwDfdhz4ue/Ktrhfvs' .
        '13jZFA8nxxMBnzWrJ6hJIqOzC3Hl2pLMqLKX4ArcJIGNhnIJ7sCvQdT6F6Tqt9LeXSzmaXHFwykDbltW+CcYSeoyyw8SNIsWccKJ' .
        'GX9+Mo34SCqjx9vyVNM8E9u4e0kK4YcAABOM4xv2429IoL/4caBnPVz+Pvtdf+HWgf7Kf+LTl4Tlep/x/wBCGNRjpSCsumWDIDxE' .
        'HB263GfTS6u1ubARL1KlVwFLAgHx76EN9zfQGOTHceqWmP3NtAJyUuP4v2UpRwzq5v8Aj/o1BLhFPpTNiLT7dzEWGpW7Dq3BGOLt' .
        'FaLpMM6ZP4wyj+oaG2/3PtFtp4poxcB4nWRffO0HI7PCinSMZ06X/lv/AGTWOfw1BRxmmNNMqaZto9p4s3zGuLVP/wAgtyPio2f3' .
        'TXenke5NkP0iP6pqexhzqnWjGFUiuVcL7Gjfqf3KejpnX9ZaNzxiVRwKOwjmf++yrHSsOOj93x8RXhGRnG2RXOk2s41jVrh0aNHl' .
        'CoWBHGAM5HhvS6Wq46PXezPsuyZYnzh2V1dO0pK37k5d/wCDAiMSXdqrtwKZk4mxnhHEPoq5rjY1m+GcgTEZ9lVY2aS8t4hG/HJO' .
        'iqChAJ4u/FcdJJUGr6oWl4DDKTwqRliSBj5c16nWTjrTT9jr/wAbJY02yu8nHxoqPJISUEaIS3L5qM6X0hubmCOxu4LW6txb+/rG' .
        'rYKgjDE9mMdnOgtpeaekU8UjseOMSPKXVDK+No8AHze89taBNAgtzawXU0n4VCY7Zo262NBjiYni2GdgAOyvHyZW2LqZzyyFpSlZ' .
        '9UGR/j8v0UqbS24rjVCF28vlA+SlXvshLYGyjMl4MZ/Drj51ot0aZ7rVLOCWeQRWQaeNMDBPwcE/9VCX/G3f7dcfOlXujz9XqU8m' .
        'ccFo7exkNeV1P1n9vwc2QM3HTC+TQFv0soRKt2LeWNmJA7Mj11ldJQSXlnaSoYw7cJIOcZLYPsA9tFbuJvvd1ONiQw1QSjfmnWLn' .
        '56nezEep200Kc7lIR2fBBB39Wa5ZycVscznatGq0Oyt7G3KWkKxRvKzELnBOOdZ2/lSK5mR55IyJW85OQGfHatVYEiMcQ4TxHYnN' .
        'AtQ4Uu7gHJBfPCeXqrnzNvEmyJ/oOtGnV55QspkPBkkgb+ypr5MX1vwkZeJgPT2fLUNkcOSE4QV57V3fsfK9PKnB4h7OMVeH6ZeL' .
        'eJfiTN2s5xgvJj1hfqq43KhFxIYpnk6zEYdRgnZSS2fmFWodQt3UZmj/AHhVPZnTDgmd8ZzUPXcJNKS4gIPv0eP1hVKS6gXnPFj9' .
        'YUJlALpxfG01vRZEIBNsx5eIqWfpV11sRI2WI32qfWYui2teSnUp3aa1i4F6mUg47ccPP1UOGjdByMdbfkfrzVqmTQMtp/KNB6Ys' .
        'Nx5NB8716zbnMEf6o+avOpbfova6Zf6Vpd69tNqkYV5LlmwiqTgnixtuRtWutdd0vgRF1O0bCgbSrnl6aTYIzf3TWK32kEfkT/PH' .
        'WfjkPCN+yjX3S7mGWbRpUmRo5Em4XByDvH21Qsej2q3NpHcW9oJIZVDxsJlGQfCqXAnyV0RpnHaBUrwjhbberFzpt9pcUct/brBG' .
        '7hAetVsk8th6KhF5blR76hyMjzqZJHdTXBsFikuJmhRR7yGwDwjYbc/X3VWudLt5bjFvcwQW1ukMZlZgC/GSSSO9c4zXV1cQMh9/' .
        'XH61Bp7u24xmSNj3kg0PYaYdjtdJlt7eUXaxCaNjIjvgwsF2PDjPMd+/FQ3ASbzOFS6rnqXJxkDiGfaD6TUCS2/CMSR4Axswrpbu' .
        '3495k/eoFZYlmuGwvlE2/EAA5OeL4ftAHsqwNQvnZZvdCZplIIfiBIIzjs7OJtvHwoh0MmspNWnaaaI9VBxBX3B3G48Rv7an6Ue5' .
        '1pZr1rBbyWQrEsaqmDnJJA5DGBvzzUa/VpLrazPyLJMtnFw5W2YLGqjJKl+LGO0k49Nej2vS+O6iWSPR9VMbEgN5PnkcHt7687ju' .
        'IRJF76oYSxkedy84V69GqRF0jUIoYnA2G+5PtrLqMjxqx46YNXpPEyknTNVAHfaNUMvTCCNeL3L1c+Asmo8DtTE1l4+yZVID6D0r' .
        's9cvbmyhhuLe5tlDvFcR8DYPbj2e0UdG4rySW6udP+6ncXdtDLOvXdXOkaliIyqgsQOwEivVTMqAAnlXSnasl7ExoXrYM1u8a7ko' .
        'R7ankuwCRmh0l6r38EQPww3yb1M+Bx5INObOkWTfpuf6polpjK3nhSC7kb+ArAdHtcubm5sbNZeK3kYgqUBxkHl21tNLummmtQyo' .
        'pLSAheWw51Ca0oqSd2ZzWtT1GDULpbbUJ41W4IC7HAwNhkUNbpXq0UxUaqmxwRJGhPs2oxOsA17UZLiAXAh4pUiJIBYFBnPfvWRh' .
        '6PXmvanrD2oQCC5cYPiTt7AKy3syy5dMqSPReiuvJqOjWr3+oWvl0pIKB1Uk5IA4c0auI4UheWWJX4FLHzAScDNeQQ9FNa0nU7a9' .
        'WxExt5RJhTw5weXKtwvTgiMrPo99DLgji4VkQNvz4TnGx9lapwe7JWVNc0Vbrpjokc9zA2iTytbAtJiGMgKO3nVKH7oPRgDq/Ibi' .
        'KP8ARVcD1Bqu6YLae+1Y3UoRn1YZUDKnEYwPXXB0HT7u04Uks3ZrRSpeFchmfnt3cquOOMqbOrHBuKlq+CppIKzaoCRtfy+HdSpt' .
        'LGZ9UyynF/KPmpV9Cyk13B0n467/AG64+daIaVYXU0V1JEgCz2csMbk/HOPYNqHSn327/b7j50qKS5RbcWct0LZJZl4XCtxs3LhB' .
        'HLnz8K8vqKeZ3+34OfJfsbG606C5tmjnndesilWURJkHjweZ7QRmurLSYre2t4Gv5HME5mDzAM7E7YJG3fVWTTNSV5+p1dk61iDH' .
        '1YKouMKFzyPfXUdhqwYMdXCEDDKYARjfGPHcb1zvSzkUKVGhsLdxbr1E0TrxE8XVkDOdwBmoNRso0cSkCWeQ+apAAHed6vaMksel' .
        '26zyiWYL75IF4eI9pxVPWLyKO9ihLyFwhPVx7Zz3nn2cqhwi1RtoWkGXEdxa56pBKxPb72AO8Cqkt4J57ZhC69U6gkjb4a0chtbq' .
        'dfNt0t1P5ZyT6e2ppNDjmiKTTSEllIIOMYIP0UlFRVIIxpUhyitdkDhAZjJgDfAGPnJqxbqeI+94APwmIz8lDreV2Ysqxu6Fowzt' .
        'w4UHb01YhZ5GwGhYjmqyE4+SsJTtm8Y0gixAwNtziglvfXfk/Fch+sJ2xbk7eyrjwOJIiyxqFfPErkkHB8KqaBcTXNjxPPI7BuHL' .
        'HwH104uwZbjhvJkWRJIVDDKq8W4FM1pqXNbi2H/o1O0cvMSye2oHWcf56T96tU0SVNQvbvSjELgtMJScC3tGfGO/HKqWo6/MukXs' .
        'tv1kc8KxuvXWxTIL8JwDz7qJLBLPJ1ElzcKsgIJSQgjY8jQzpLpEendHL50nu5nk6pff7hpMe+Dlnlzoqxo5n0m4adhY63d6fAAF' .
        'jtoY0KRgDkMjvqeHo5qsihj0q1LB/o4x9FSNdwLdSRGZDIjYZVOSpo1bX0DQqS/DgfG2qlYtgL97Op/71al+5H9VL72dT/3p1L9y' .
        'P+7WhguYbhWaCVJAp4SVOcHuqWqAzX3s6l/vTqf7sf8AdpfezqX+9Gp/ux/3a0tKgDNfezqP+9Gp/ux/3af72dQ/3o1T2R/3a0lK' .
        'gDNfezqBxnpPqm3hF/dpfexfgkjpPqYzz82L+7WlpUAZhujOoDl0n1PPfwxf3au2avbRLA08k5i80yyABnOeZxtRg8qBCUm4YDkJ' .
        'D6964OtfpSNsMbbDCnzSahklw4FOrjqmxk4zVS4k4ZBnurjcrVFRjbMjpT9X91DVATgtbt/ZStBe3jJnGay9i/8A+07rPxoH/sKf' .
        'oozqDnevWx7xMJqpFWfUX4sZJrjT7w+6UTsfgK+59FD5nPGae1bEkjdojanPgIme6MymK7s5FIDruM16Vox4r62IGPhkju2NeXaE' .
        '2Li39H0GvUujqmSdJByQuKxt6kjeS9Ngzjhj13VnmhNwoV8JkgHzkzQHo7bCfUtdbymGCTyk8CyMVyOJuTCtQU0waveyrfQddKxS' .
        'W3u1CrzB2O3MgbnND7PoxcabNqMlzDLcJeTGRWs3BEanOxVscWM9grOS1JpHD1GKU3sEEg1e3OYnlZOI7pIHHM9hqKW+uJlMGoFY' .
        '45AytJJDwsikHJB5ZquqQJKFh1BIZDv1VxxWz8zsM7HerF17srbSwSxzyLIjJsocHIPb2cxXPUo9zi0Thyn+StZae5uLuWHM6y6s' .
        'zebzThThOapvE66fIHidT5COaEf5w1dszd2IYHrYHk1GRwjbcSnIB9BxSi1W6Sz4n4JeGzhYB154c8/VXtYXLSmj6XpnkWGLSKOl' .
        'fjtT/bpfopU2kgCbVM8/L5c+0Uq9lnHYMnOJLsn/AM9cfOtAOk7ssVsAcYcuO8Ecj8tXukV41oZeGYw9Zf3OSq5O3ByNZuSS1mfi' .
        'luJ5WznLb/JXldSv9r+34MpPc9s6xZlR4WDJIoYMpyDkZ506kpu+wPxm7K8b0zTE1i7jsrETTTt8EEeag7WJ7AK9Y0LoXoWlwRJJ' .
        'Ypd3IHnTzIW4j6DsBXLOcYbMhY7DB1vTtM0xZbm7jARAzIrBn37lG551b0u4h1C2i1GOBojcxgjrFAfh7M1AmkaVxFhpVoGPM+TL' .
        'k/JV9UjVAioAmMcONsd2Ky8ZMvSDr/pHp9nKIFmW4umYIsMTAtxHkD2D10VjLNGpdeFiN1znFQLZ2q44baEYORiMc6sZq9aaCjNw' .
        'TIhl3I99cbLntq/BcouCvWgHn72RmiEvVRxszKoUDJ2oAt/ErM6oSzHOC2wz3VjKO+xaewVadZDEF48lu1COw0J6LZWwc9vW/QKe' .
        'TUZm4MeavHg4Pgah0G7hgsiJZo48yZAZtzsKcEKRqEIYU5RTzFDI9Ws0xm8g3/pBVlNUsnGRdQn0OK0RJP1KiRWHMfVQjpmM9Hpx' .
        '/SRf/ItFY7uCaQLFMjt3Kc0N6XKW0KYDHw4+f661XCBcmQ0slOlOpcMbM/lTs4VcnAIGfmrTatdyL73DHJJMN+BVzjOOyhWnXVnb' .
        '9JtSSCSLrbt+KOeOQFs4BK8J58s7VJruq23U3FxO8HBG4UPG4JIBzgE487fOO4Vot0ZyO+itwbaDVEIMbi9PmMuCvmKe81q7aYTR' .
        'BhzxvXm9nrlhI9xNDdRAXE2VBIDkABQWGOZxW40eRZdMt3CluNSeIdu9ZzlpVlpWFc0s1S4othwv7GrotHjk3y1h5hdi9JbzSqoO' .
        'rPJXPtrlxH2xv8tPzC7C0l3NKqAWFG41hYN371Su+kFjZzG3uruOGZfivnJHYeVXjy69gcaDTOBsTWShkExZONsmRiCD4narD9Jt' .
        'NDf47Ht6d/krO2l8kbLI0qpG0hJZjgbnxrl6y6R0dLyza21wHteIHsNCrmfhllBY5KlvYcVDa38UsTCOVWRFYcSsCCdqG3N75y4Y' .
        'HIC/XXBpaOmMVFtguwcH7pztnYwv/wDEKN6i4yfRWSgv4bbp0Lu5kEcQQqzNy3jx9FFbzXtNkJCXqHbub6q9rH+lHnZP1Ecj7mnt' .
        'W86XxjNDm1K0Lby/Ian025hubvq4H49iDsaqS2EgDokg8ot8kcvoNewdFOE6e0nfM4B9deLaHMEvbRsK/Cwbhc+a2Ow17Ra3Nte2' .
        'UdzbW7dUxKMiLgxtncEd2awl6ZWjW7VGV1heO+ucKzAzv2E1StV1C2a5k06Wa3EEXWMFLBW3A2XBBPqrYXF+lvKyyWV0EGwkVMg1' .
        'xHqNhNssgz3NtXOssVK2bRgCoOlr+56W+raY99Mg98fCcDnOxAP1VY0N9M1Sd4rGK+0q5CmRljkIQjPrU+yio4WHmRgjw/8AuoJo' .
        'RKuGtgw8Rn6a1llhL2CUIPhUWpLHUEEYkkttRWMlgbgdW/LbdQQe3sFB5bCFYWims7q1PUwwgqONDgksQRnYeOKvW/X2mPJ4+rA3' .
        'KZPD6MZ29VG7C7iul2UxSgZaMncfWKvFJ36WZ6p4+GYDSWHX6puDi/lHL0UqbRSS+pnvv5vnFKvqGgUbAmq2FzfzS+SQ9cY9QuQ3' .
        'nKOHPBzyR3VUHR7UtiUtYt9y9wm3pAztWu6O6MurTauGneLqb+XHAAc8WOefRRodDIDzvJv3FrxesyOOZpft+DJxjZT6NR6N0fsj' .
        'HDfW8txJgzTkEFz3ctgOwUaXX9OBwb+DPjn6qpfeZBje9nO/5K/VXQ6HWwH+N3Gf+n6q85pvdlWkgnHrmmyAcOoWxz/SAVZivrWZ' .
        '+CO6gd/yVkBNAp+iltFC0gu5RwecS5UDHpxtWbjmhYI3uHf3Ck+aEmjcN3Y7SKqOOyWelCnrzDS+lF9pFrBHHEskV3MwRJM+8kZy' .
        'vf2VNc9NdZuLY9RDFbHAJYIWI5ePjWulk7BXpHrLX13LptqwEEe0jg7swO49VDRZsV4jI4HiaraTdwQy3Utw3XScfCjY577k0Tiu' .
        'xK3G5JUnlinpoV2VlgKkEOx8cmrenaeZlLLE5TPPBzWn0vTohHHO6DjIyoI+DRJFIG/szmqUQM7DoMPAGm4wO7hzirsGh2ybrx4P' .
        'oovUMlzDGcM44s4wNzVUIqR2kcZ2jZccjg5+SmuLHr4uF4+JeIHhYkg+00QRg4yM+uusiigAjdHbK6BM1pFGQfMKIAaqnoNo7g9b' .
        'bqcnPmjG/f6a01NT4CkZuboxp1jAZ4YpGeEFkXnv6MUQivFtrSFp5LeDiTIWV+r39FWnRkuuvkcdWoIUAdpxWfuOjnurq11d3Ekf' .
        'ksrLwZUtJsNwDnAX1E1GRXEI0mXn6R6eg3vbYnuVyfmFRN0r01f89xeiN/qpHojpK8o5fQJmodfaboFnfQWJEsl1cZ4IVucN8/pr' .
        'k0I1CK9LdMPOSQemJvqp16U6W5x5Q6+JjaqJ6O2v5pu/5tf71L737ZRkaPeHw8rX+9RoTC0Ek6Q6a+Pw2MZ5cXEPooVrog1KeKS0' .
        'WG8Ujhk4HAZccvVua6l0CE4I0a837BeIMf1qng6K2F1ExuLOe2J24TOGI8QRnBq4x0eoTdgGbREwSsYQsMgMf71Ar3Tr+W2SLqRg' .
        'SoWHWL8EGvSbnQILiEIZZlZVCiQPkkAbZ23rNXPQ6/V2it2ikQDiWQtwb55bVq9ORpii3BbGPbStQhiPUxcKtKxwsuTv6KaCC9t5' .
        'WadJNxjHCSa1qdD9QtRx+WCNhg4Vjj24rQWkvkNqGu4/KJPy4fOJ9Oa0lBSjRKm4ys8pOn3N5qfWtbXIjO2REe6rcujtDjjRh6tx' .
        'XpDaoj3R/B5ymMggb/VQ3VpbW63ljZT2ExYYesGnCKiqRDm5O2YhNLJjISVF/WNVn0u4WUjrI5FxzXetZFo8N5CzR3UI4eYYcJFV' .
        'z0cnVyySIM8nANUwsydvYy286SqsjMhzwiPFeh9E9Zit7G28plW3V3kDpIcdpxnuoZHp99bgCVhMB2qN6ivDD5NJHc2jvkciuM+u' .
        's5RXJak3seg8JfL28oMROASCSD3fbUE9is/45IZM96VF5PDe29sesmhMajgaKUqy7Aev11Fd2OqRRStbXrXL8PvYdVDA+O2D8led' .
        'LTJ7HTG4s4l0e3iQyK7WoQZLiTCr6c1SN4kRwmsxSjO3mFsD0ioF8tnhb3VYqkM4OOAAZC7ZHIjiYeyh7afrTjH+DlOeQQnHrAwf' .
        'VW0eni1bYp5mnQVXVrcfjLni8ViY5rkazZRnrfKrlDHkhuo7uznQ1dL1cvxB7M88jhb1VodG6Mo9oJNRkjuZJMHhRSqxj8kEYJ9J' .
        'rWOGKqiXlb5M9o4Il1PmPw+bt8RSptHPHLqbY538vLl2Uq+nfJa4DfQP/GNfOOd+1a3J7qyPQNgLjX+Q/D2rWdavLiX21891/wBd' .
        '/Yya3Ot+6lvTCRTyZT6DQPpVrg0y16mFh5TKNt/gDv8AT3VyR3JB/SXUXv7k6ban3mM5uWBxnt4QaHXyyppl15PmF1hYoUlIKYGR' .
        'iodBlI0q3Z1XrJgZWIVs7naiD3EPVukjFQ6lSSCMZGO2mrUjXbSZKzt7rUdB0xLOFrmeK8bhXPnduSfDzuZrTfenqNhpd8JNQsoU' .
        'kt8M7BsDkTnwwOYqj0P1nT9KjmWGQrCLlxDHcOFkKMF3Pecg7UK6WdJLzXL9rNfeYEYKsattk8mPf/3jeupNrYwdclnTNPklZEtp' .
        'FuFc4idVI6zvbB5Ct3onRxLNVkvCs0/5IHmL7aXRfTrS0s0liDNKRw8cqcJwOwA9lHQy8WAd6qhJocAKMAYA7KemzUc08cIBlkVA' .
        'TgcRxmmMkrgxLktwJxHtxVae84RlVl4efGqgg+2pLe7jnTiHEO8MN6BWidFCjA2FdVCbqEMFMqgk4AJxmpc+NAx6anpjQBUnRrwN' .
        'GVeIIwIYj4VdxRNDEqZDEdoGPkrvj6vPWYBJzsaRkBUNgjPeKyy/pBckFzDLKjCNjE5BAfY8Pjiqmj6NaaQj9RGzTyHMtxK3FJKe' .
        '8t9HKiPWDlv+6aXGPH2Vy3tsXbFkjkp9tM5fgOBk+nFdcQ/7FLjA7/ZSTp8gVTdTRECSGQr2tgH5RTRahFIz/EAbhUuwAk2ByveK' .
        'tF+7NY3p3pU1/Jak27vpyhmm8nt1kkWT4rEHBK45gb1otM3Qtw9J0l0eJuGTU7NWzgr16nB9Rqae/LWLXVhJBOindlYOpHbuCB8t' .
        'YLQLBZtYaG2tLqyDxEG8s4JbbhK7+ekmVOfCtJpPRaPTtTGope3rvjheLq0RZB+mFAB586rRGDtMC/Hr4jTDW65HMxtsa5bpETul' .
        'qeHHxj9VVrezNxPPClwkUsTYZHi7OwjwNdtobSD/ABqJiOxVxWyd7kMGwvp95q0iuXs3cebwuSpPsoi3R9CMjUGJ7nQMPlqtHocj' .
        'XsgLxqAOYGTmiP4bZ27DjS74R5o+AQPTWiM2UH0A5AS9Kn8pUA+Sne3ltEZ7YRTyKvnhJChP/SNs1ENeyCJLMejrPsqpLrEGS3uc' .
        'hbGQTIRvTEiFtWkaQDqIQwO4yc00t/5QnVy2q4weearzX0F24/AzBJnJdJOfp2ojZ6aZwphunYYO4wcenBqWaIo6bq11YE8ZaWA8' .
        'kbGV8A311qtP1WG5Tijf0qx3B8RQMaNGrMHut89qbGoX0ZYX4oLoAjkwXGK5cmCMt0bRy+zDvSiXGkFywTiGM5AGe7J9Jqpbx9Tb' .
        'wwJskUSqAcEjblntqhqeq+T6Zbi7tkvng2kVzwo5JxkHtIGTUsOrW90qy24aWMnZkIYZ9PMVbuEEgik5MJQk5+Eo9VFbBm6xffNi' .
        'POXhxms+l4RjMcp27sfRVyHUQhzh1OO3sqYz3KcWZrRgFk1MZx+HzbesUqfSMddqmOXl83zilX1DGroMdAvMuNf4ts37EZ9da3rE' .
        'Hxh7a8vv5LvTdT1GGxYsZpvK40XfromHNe/BHZQl+lV0uQZQCDggkgivD6zHOWTWls0iHp7nq2p6zbWFtJJxq7qpIQHme6vNOkz3' .
        'DKjyuTfXS8bj8kMcKtCpNa4rqO6dw4icP1bPsxFNe66LzUl1GVI1ZWUrEpJXC5IHfzrGOGadshyXCNnLpmkxvFb9RdSFVAY+WyAD' .
        'A7AK4i0Po9I46zSS++AZLt22rNr00KScRtLbJ5+e2/yVai+6A0ciN5DZYXsyx+io8PJZWqI3Q+106bTLi4vbaO7u47jgiMkrAKAB' .
        'vt89HJLbTJOk+mWNppUK3WRPI4lbKIuc8+Y8O2sfoPSVtEspoIobaZ5Juu61/hocAYXbwo99zi7fV+mOoXdwcyTWpbbfh85RgH0V' .
        'ppnqtshtOOyPTrWzW2zwyOy9ik7CpVijRuIKAT3VBJaxnPFECD25xULWSMQERCoHIgGqsi/2LssgQE88DOAd6zF/Ob65614JGVfN' .
        'CY4hjt7OZo3ZWQgcsYAHbbiGMAVeVFQYUADwFMKbMvFb24BK299Ht8SIgeyrNvCXy0aBl5HiQg0ekLgZRQx7icU4JIGRg91AaQba' .
        '2kJuOJoeHg33G2fX20TGPCgepXizS3EREbxwgABhnftNOnDIq4ij4eEZyDv8tOmF0Gy6jmwHrpusQnAZc92aCXFysZRVihHMgqck' .
        'VD7oXKjd8gDkAKKYamE7sySTkIkjIEBBUZBO9R22pWsc/kEkyrcxgEodue+1X7UEW0QOAeAch4VhOk00sPSSaFb8WpMIk690UMFJ' .
        '+ArY2G3y1nNSapFxSu2bXy62wT1owOZwce2mN/aKMtcRgeLV5WZDcFnk1KOQqfhSXGfn5VG0UTnzr2y5ds43rF4Zv2LuJ6m2sacv' .
        'O9g/fFQv0i0hDhtQgHrrzDyS1Oxu9PHh1oroWsC5CXenfxh9VPwJdguJ6cOkOkNsL+E+uuhr+lk4F7Fn015esEDc77Th/wCt9lc9' .
        'Ra53v7A5OPh5p+FLsK4nq66tYP8ABvIf3xXflkLKWjcSY7EOc+ivJ2trJGYNeWGVGchs5rhlskAfy2zBznzX3+ak8EgtG5ne8utU' .
        'kvLPTrqJ4gFJfC9Z6s9g+eizX6IqG5zbyOMlZduXjXmomiADR6jHleRWYjAoz0YvpL7WYtPN7LeW8kTs4lbi6sgbEb5/75VooyS3' .
        'E6Zpo7y0e5kDXERHLZ+2k95Gje8z2/V9vHxk/NQa6so/dPqJAGdGJbb11ZaxQjiQMD4GtVFmNFbUDFcu0g8likzjMc/Fxj0EVWOn' .
        'XiqW8kZkI2Y439FWpLAZ/GJnxbFcRubSQrIztDjHmMcj0UaWMGLb8EgUiSJyM8JdcH5auWNveQziW3WQHG5UqM+HOja9R1EchM5X' .
        'HwpU4vop4ZSW4rSOCVRzHHwH2YpaX2HuUVvbq3UzTQSBowxUSr5uewnHOjGha4uq28hZohPbtwXCRtkDuYeFCry7/BLkSItvIIzg' .
        '52B7Kwdl0iXTr5rq0WO2BLKxi2YgntHxt996iWLVEak0bv7oD+96Un5V2gPm7Dzl3o4szrI/IMjEMQoAbxrzbU9Vju9PBXUXuhHc' .
        'LKGkkyUOc+nsHhUUnSfUZCeLVbjBJyOsXf5Kzl085RSNYzSd0eqNKWTkzAju7KoGS7VzERM2AeBuE+cPHxrzlek2pAALq04A5DrF' .
        '+qp7bWdZunz7q3iW6H36bjHDGMb8xjPcKUOknaH4q9kF9JBM2qYH+ny/OKVTdH3t47OWe9Ihe8nkuFVgc8DHb5qVfQyyJOivEgvc' .
        'h0+CLVZV028QSQIC8TcniI7FYch4VJNpUkEnBFqupooGABcch7KVKsP8dviVnF0DvBGznyCcgk6vqef2j7KY2NwDtrGp/wAf7KVK' .
        'vS0R7HdSOvc+fP8AlfU/5j7KbyCf876n/MfZSpUaI9hUhGwuPzxqf8x9lM2nTnI919Uwf+I+ylSpaY3wJI49zJh/rfVP5n7KcabN' .
        'n/K+q4/aT9VKlVOMew0h20yYjfV9U/mT9Vc+5cv531T+Z+ylSpaY9h0OdLlA21fVR/7k/VTHS5TjOr6p/NEfRSpU9MexNHY06fH+' .
        'WNU25fhP2U3udP8AnjVf5n7KVKloj2HSENOnB/yxqn8z9lI6dPv/AIY1T+Z+ylSo0R7A0jn3Mlyf8L6rz/8ANH6qdtMlZQp1fVCB' .
        '/wAT9lKlRpj2HR17nz5GNX1P+Y+yl7nzn/W+p/zH2UqVPRHsJof3PnH+t9T/AJj7KRsJ8D/C+p/zH2UqVLRHsCSGNhP+d9T/AI/2' .
        'UvIJz/rfU/5j7KVKnoj2KpC8guPzvqf8f7KXkE/531P+P9lKlRoj2CkN7nznP+F9T/mPspHT5yuDq+qb8/wj7KVKhwj2FSsQ06fA' .
        'UaxqmMf+Z+ykdNmJI92NV/mT9VKlRoj2FSHOnzjlq+qD/wBz9lL3PuPzxqn8x9lKlS0R7BSG9zZufuxquf2k/VXXudM3PV9U2/4k' .
        '/VSpU9EewUcjTpyMe7GqYH/E/ZTiwuN/8L6n/MfZSpUnGPYEkI6fPz919T2G34R9lLyCf876n/MfZSpUaI9hpIXudOTg6vqZB/4j' .
        '7Kkl0eBLcXVxNc3jxAFVuZS659FKlWWTaLaIybQdFCWRpnMkhLM3M8qVKlXyc5y1Pc+MnKWp7n//2Q==';

    public function getWriter()
    {
        $phpWord = $this->buildDocument();

        return IOFactory::createWriter($phpWord, 'Word2007');
    }

    public function download()
    {
        $objWriter = $this->getWriter();

        return response()->streamDownload(function () use ($objWriter) {
            $objWriter->save('php://output');
        }, 'template-bank-soal.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    protected function buildDocument()
    {
        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection();

        // Judul (rata tengah, sesuai template kiriman guru)
        $section->addText('TEMPLATE BANK SOAL', ['bold' => true, 'size' => 16], [
            'alignment' => Jc::CENTER,
        ]);
        $section->addTextBreak();

        // Identitas (berlaku untuk seluruh soal) — contoh isi
        $section->addText('KELAS: VII');
        $section->addText('MATA PELAJARAN: Matematika');
        $section->addTextBreak();

        // Petunjuk: baris ini diawali "CATATAN:" sehingga tidak ikut terimport.
        $section->addText('CATATAN: Ganti KELAS dan MATA PELAJARAN di atas dengan data Anda. Hapus baris contoh di bawah ini dan tulis soal Anda sendiri — contoh tidak ikut terimport.', ['italic' => true, 'size' => 10, 'color' => '808080']);
        $section->addText('CATATAN: Gambar dan tabel bisa dipakai di SEMUA jenis soal (Pilihan Ganda, Benar/Salah, Essay) — letakkan gambar/tabel tepat di bawah teks pertanyaan, maka ikut terimport bersama soal.', ['italic' => true, 'size' => 10, 'color' => '808080']);
        $section->addTextBreak();

        // A. PILIHAN GANDA — contoh bergambar (sesuai template kiriman guru)
        $section->addText('A. PILIHAN GANDA', ['bold' => true, 'size' => 13]);
        $section->addText('CONTOH: Observe this picture and answer the following questions');
        // Gambar contoh (kelas/meja). Bila file belum ada di server, dibuat
        // dari gambar tertanam (base64) supaya template unduhan selalu
        // bergambar — file biner tidak bisa di-push lewat konektor, jadi
        // generator membuatnya sendiri saat pertama kali diunduh.
        $gambarContoh = public_path('images/contoh-gambar-kelas.jpg');
        if (! file_exists($gambarContoh)) {
            @mkdir(dirname($gambarContoh), 0755, true);
            @file_put_contents($gambarContoh, base64_decode(self::CONTOH_GAMBAR_BASE64));
        }
        if (file_exists($gambarContoh)) {
            $section->addImage($gambarContoh, [
                'width' => 425,
                'height' => 225,
                'alignment' => Jc::CENTER,
            ]);
        }
        $section->addText('Ika: How many desks are there?');
        $section->addText('Bintang: there are ......');
        $section->addText('A. Five desks');
        $section->addText('B. Five desk');
        $section->addText('C. Two desks');
        $section->addText('D. One desk');
        $section->addText('Jawaban: A | Bobot: 10');
        $section->addTextBreak();

        // B. BENAR / SALAH
        $section->addText('B. BENAR / SALAH', ['bold' => true, 'size' => 13]);
        $section->addText('CONTOH: Bilangan 17 adalah bilangan prima.');
        $section->addText('(Benar /Salah)');
        $section->addText('Jawaban: Benar | Bobot: 10');
        $section->addTextBreak();

        // C. ESSAY — contoh memakai TABEL (menunjukkan bahwa soal boleh
        // memakai tabel; parser mengubah tabel jadi HTML <table> yang ikut
        // terimport sebagai bagian pertanyaan).
        $section->addText('C. ESSAY', ['bold' => true, 'size' => 13]);
        $section->addText('CONTOH: Perhatikan tabel nilai ulangan berikut, lalu tentukan rata-rata nilai kelas.');
        $tabel = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
        $tabel->addRow();
        $tabel->addCell(2500)->addText('Nama', ['bold' => true]);
        $tabel->addCell(2500)->addText('Nilai', ['bold' => true]);
        $tabel->addRow();
        $tabel->addCell(2500)->addText('Andi');
        $tabel->addCell(2500)->addText('80');
        $tabel->addRow();
        $tabel->addCell(2500)->addText('Budi');
        $tabel->addCell(2500)->addText('90');
        $tabel->addRow();
        $tabel->addCell(2500)->addText('Citra');
        $tabel->addCell(2500)->addText('70');
        $section->addText('Jawaban: (80 + 90 + 70) : 3 = 80');
        $section->addText('Bobot: 20');

        return $phpWord;
    }
}
