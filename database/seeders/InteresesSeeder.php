<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PerfilInteres;
use App\Models\SubcategoriaInteres;

class InteresesSeeder extends Seeder
{
    public function run(): void
    {
        $perfiles = [
            'Informática, información y obras generales' => [
                'Sistemas de información','Inteligencia artificial','Programación de computadoras',
                'Algoritmos','Bases de datos','Redes y comunicaciones','Ciberseguridad',
                'Bibliotecología','Archivología','Museología','Periodismo','Enciclopedias generales',
                'Publicaciones seriadas'
            ],
            'Filosofía y psicología' => [
                'Metafísica','Teoría del conocimiento','Epistemología','Ética','Filosofía política',
                'Estética','Psicología infantil','Psicología cognitiva','Psicología social',
                'Psicoanálisis','Lógica','Filosofía antigua','Filosofía moderna','Filosofía contemporánea'
            ],
            'Religión' => [
                'Teología natural','Biblia','Doctrina cristiana','Teología práctica',
                'Órdenes religiosas','Religiones comparadas','Budismo','Hinduismo','Islam',
                'Judaísmo','Religiones indígenas','Mitología'
            ],
            'Ciencias sociales' => [
                'Estadística','Economía','Finanzas públicas','Economía laboral','Comercio internacional',
                'Sociología','Antropología social','Cultura popular','Ciencia política','Sistemas políticos',
                'Derecho constitucional','Derecho penal','Derecho civil','Administración pública',
                'Asistencia social','Criminología','Educación','Sistemas educativos','Métodos de enseñanza',
                'Comercio','Mercadotecnia','Comunicación','Transportes','Folclore'
            ],
            'Lenguas' => [
                'Lingüística','Fonética','Gramática','Semántica','Lexicografía',
                'Lenguas clásicas (latín, griego)','Lenguas romances (español, francés, italiano, portugués)',
                'Lenguas germánicas (inglés, alemán)','Lenguas eslavas (ruso)',
                'Lenguas asiáticas (chino, japonés, coreano, árabe, hindi)','Lenguas indígenas',
                'Enseñanza de segundas lenguas','Traducción e interpretación'
            ],
            'Ciencias puras' => [
                'Matemáticas','Aritmética','Álgebra','Geometría','Cálculo','Análisis numérico',
                'Astronomía','Astrofísica','Física','Mecánica','Termodinámica','Óptica','Acústica',
                'Electricidad y magnetismo','Física cuántica','Química','Química orgánica',
                'Química inorgánica','Bioquímica','Geología','Mineralogía','Paleontología','Biología',
                'Genética','Ecología','Evolución','Botánica','Zoología','Anatomía comparada','Fisiología',
                'Ciencias ambientales'
            ],
            'Tecnología y ciencias aplicadas' => [
                'Medicina','Anatomía humana','Fisiología humana','Enfermedades y patología',
                'Farmacología','Terapias','Enfermería','Odontología','Veterinaria','Ingeniería',
                'Ingeniería civil','Ingeniería estructural','Arquitectura','Ingeniería mecánica',
                'Ingeniería eléctrica','Ingeniería electrónica','Ingeniería química','Ingeniería de sistemas',
                'Agricultura','Cultivos','Ganadería','Silvicultura','Pesca','Ciencias de los alimentos',
                'Tecnología de alimentos','Negocios','Administración','Contabilidad','Finanzas',
                'Gestión de personal','Manufactura','Construcción','Electricidad aplicada','Plomería',
                'Carpintería','Gastronomía','Servicios de hospedaje','Turismo'
            ],
            'Artes y recreación' => [
                'Arquitectura del paisaje','Diseño arquitectónico','Artes plásticas','Escultura',
                'Dibujo','Pintura','Grabado','Fotografía','Diseño gráfico','Diseño industrial',
                'Diseño de modas','Artes decorativas','Cerámica','Ebanistería','Joyería','Música',
                'Teoría musical','Composición musical','Interpretación musical','Instrumentos musicales',
                'Teatro','Actuación','Dirección teatral','Escenografía','Danza','Ballet',
                'Danza contemporánea','Cine','Guionismo','Dirección cinematográfica','Producción audiovisual',
                'Deportes','Juegos','Educación física','Recreación al aire libre'
            ],
            'Literatura' => [
                'Poesía','Teatro','Ensayo','Novela','Cuento','Crítica literaria','Literatura infantil',
                'Literatura juvenil','Literatura española','Literatura hispanoamericana',
                'Literatura inglesa','Literatura norteamericana','Literatura francesa',
                'Literatura alemana','Literatura italiana','Literatura rusa','Literatura asiática',
                'Literatura africana','Literatura de viajes','Sátira','Humor'
            ],
            'Historia y geografía' => [
                'Geografía general','Geografía matemática','Cartografía','Navegación','Geografía física',
                'Geografía humana','Geografía política','Viajes y turismo','Historia universal',
                'Historia antigua','Historia medieval','Historia moderna','Historia contemporánea',
                'Historia de América del Norte','Historia de América del Sur','Historia de Europa',
                'Historia de Asia','Historia de África','Historia de Oceanía','Historiografía',
                'Arqueología','Genealogía','Biografías'
            ]
        ];

        foreach ($perfiles as $nombrePerfil => $subcategorias) {
            $perfil = PerfilInteres::firstOrCreate(['nombre' => $nombrePerfil]);

            foreach ($subcategorias as $nombreSub) {
                SubcategoriaInteres::firstOrCreate([
                    'nombre' => $nombreSub,
                    'perfil_interes_id' => $perfil->id
                ]);
            }
        }
    }
}
