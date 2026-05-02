<?php

namespace App\Helpers;

/**
 * Türkçe ad-cinsiyet tahmin helper.
 *
 * Kullanım:
 *   CinsiyetTahmin::tahmin('Ahmet Yılmaz')   // 1 (erkek)
 *   CinsiyetTahmin::tahmin('Ayşe Kaya')      // 0 (kadın)
 *   CinsiyetTahmin::tahmin('Deniz Demir')    // null (uniseks/bilinmeyen)
 *
 * 0 = Kadın, 1 = Erkek, null = Belirlenemedi (mevcut sistemle uyumlu).
 */
class CinsiyetTahmin
{
    private static $cache = null;

    public static function tahmin($adSoyad)
    {
        if (empty($adSoyad)) {
            return null;
        }
        $temiz = self::normalize($adSoyad);
        if ($temiz === '') {
            return null;
        }
        $kelimeler = preg_split('/\s+/', $temiz, -1, PREG_SPLIT_NO_EMPTY);
        if (empty($kelimeler)) {
            return null;
        }
        $sozluk = self::sozluk();
        // Önce ilk iki kelimeye bak (ad veya bileşik adın bir parçası)
        $bakilacak = array_slice($kelimeler, 0, 2);
        foreach ($bakilacak as $kelime) {
            if (isset($sozluk[$kelime])) {
                return $sozluk[$kelime];
            }
        }
        return null;
    }

    private static function normalize($s)
    {
        $s = mb_strtolower((string) $s, 'UTF-8');
        $s = strtr($s, [
            'ş' => 's', 'Ş' => 's',
            'ı' => 'i', 'İ' => 'i',
            'ğ' => 'g', 'Ğ' => 'g',
            'ü' => 'u', 'Ü' => 'u',
            'ö' => 'o', 'Ö' => 'o',
            'ç' => 'c', 'Ç' => 'c',
            'â' => 'a', 'Â' => 'a',
            'î' => 'i', 'Î' => 'i',
            'û' => 'u', 'Û' => 'u',
        ]);
        // alfanumerik dışındakileri (noktalama, semboller) boşluğa çevir
        $s = preg_replace('/[^a-z0-9\s]/u', ' ', $s);
        return trim(preg_replace('/\s+/', ' ', $s));
    }

    /**
     * Lazy yüklenen ad → cinsiyet sözlüğü.
     * 0 = Kadın, 1 = Erkek. Uniseks/belirsiz adlar burada YOKTUR.
     */
    private static function sozluk()
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $erkek = self::erkekAdlari();
        $kadin = self::kadinAdlari();

        $map = [];
        foreach ($erkek as $ad) {
            $map[$ad] = 1;
        }
        foreach ($kadin as $ad) {
            // Uniseks koruması: aynı ad iki listede varsa kaldır (null kalsın)
            if (isset($map[$ad])) {
                unset($map[$ad]);
                continue;
            }
            $map[$ad] = 0;
        }
        self::$cache = $map;
        return $map;
    }

    private static function erkekAdlari()
    {
        return [
            'abdulbaki','abdulhalim','abdulhamit','abdulkadir','abdulkerim','abdullah','abdulmecid','abdulvahap','abdurrahim','abdurrahman',
            'adem','adil','adnan','agah','ahmet','akif','akin','aktan','alaaddin','alaattin','alemdar','alican','alim','alkin','alparslan',
            'alper','altan','altay','anil','arda','arif','armagan','arslan','asaf','asil','asim','askin','aslan','atak','atakan','ataman',
            'atalay','atif','atilla','attila','aybars','ayberk','aydin','aykut','ayhan','aytac','ayvaz','azat','aziz',
            'baha','bahadir','bahattin','baki','balaban','bahri','baran','baris','basaran','basri','batuhan','batur','bayar','bayram',
            'bedirhan','bedrettin','bedri','behcet','behzat','bekir','bener','berat','berk','berkant','berkay','berke','berkin','bertan',
            'besir','beytullah','bilal','bilge','bilgehan','bilgin','birkan','birol','bora','boran','bozkurt','bugra','bulent','burak',
            'burhan','burhanettin',
            'caner','cebrail','celal','celalettin','celil','cem','cemal','cemil','cenab','cengiz','cenk','cevat','cevdet','ceyhun','cezmi',
            'cihan','cihangir','cihat','comert','coskun','cumhur','cuneyt','cuneyit',
            'daghan','davut','demir','demirhan','devran','devrim','dilaver','dincer','dogan','dogu','dorukhan','durmus','durul',
            'edib','ediz','efe','efecan','efkan','ekin','ekrem','eldar','emin','emir','emirhan','emrah','emre','enes','engin','enis',
            'enver','eralp','eraslan','ercan','ercument','erdal','erdem','erden','erdi','erdinc','erdogan','eren','erenay','ergin','ergun',
            'erhan','erim','erkan','erkin','erkut','erman','ersin','ersoy','ertan','ertugrul','ertunc','erturk','esat','esref','eyup',
            'fadil','fahrettin','fahri','faik','faruk','fatih','fazil','fazli','ferdi','ferhat','feridun','ferman','ferruh','fethi',
            'fethullah','fevzi','fikret','fikri','firat','furkan','fuat',
            'gani','gokay','gokberk','gokhan','gokmen','gokturk','gorkem','gultekin','guncel','gungor',
            'hakan','hakki','halil','halim','halis','halit','haluk','hami','hamit','hamza','hanefi','hanifi','harun','hasan','hasip',
            'haydar','hayri','hayrullah','hidir','hikmet','hilmi','hizir','hudaverdi','hulki','husam','husamettin','husnu',
            'ibrahim','idris','ihsan','ilhan','ilhami','ilker','ilyas','imdat','irfan','isa','isam','ishak','iskender','islam','ismail',
            'ismet','izzet',
            'kaan','kadir','kahraman','kamer','kamil','kamuran','kanat','kasim','kayhan','kazim','kemal','kemalettin','kenan','keramet',
            'kerem','keskin','kivanc','koray','korhan','korkmaz','kubilay','kudret','kursad','kursat','kutay','kuthan','kutlu','kutlukhan',
            'kutsal',
            'latif','levent','lokman','lutfi','lutfu',
            'macit','mahir','mahmut','mahsun','makbul','malik','mansur','masum','mazlum','mecit','medet','mehmet','melih','melik',
            'meliksah','memduh','memo','menderes','mert','merthan','mertcan','mesih','mesut','metehan','methi','metin','mevlut','mirac',
            'mirsat','muammer','mubin','mucahit','muhammed','muhammet','muharrem','muhip','muhittin','muhsin','muhtar','mukremin','munir',
            'murat','murathan','musa','mustafa','mujdat','mursel','muslum',
            'naci','nadi','nadir','naim','namik','nasi','nasrettin','nazim','necati','necdet','necip','nedim','nejat','nesim','neyzen',
            'nezir','nihat','niyazi','nizam','nizamettin','nuh','numan','nuretdin','nurettin','nuri','nurullah','nusret',
            'oder','oguz','oguzhan','okan','okay','oktay','olcay','olgun','omer','onat','onder','ongun','onur','oral','orhan','orkun',
            'oruc','orcun','osman','oytun','ozan','ozdemir','ozhan','ozcan','ozkan',
            'polat',
            'raci','raif','ragip','rahmi','ramazan','ramiz','rasih','rasim','rauf','recai','recep','refik','remzi','resul','ridvan','rifat',
            'riza','ruhi','rustem','rusen',
            'sabit','sabri','sacit','sadi','sadik','sadrettin','saffet','sait','salih','salim','salman','samet','sami','samil','samim',
            'samir','sancar','sarp','savas','sebahattin','sedat','sefa','seferhan','sefer','sefik','sehmus','selami','selcuk','selim',
            'selman','semih','senol','serafettin','serbay','sercan','serdal','serdar','serhan','serhat','serkan','serkant','sermet',
            'servet','sever','sevket','seydi','seyfettin','seyfi','seyhun','seyit','sezai','sezgin','siddik','sinan','sinasi','sirac',
            'soner','suat','sukru','suleyman','sungur','suphi',
            'taci','tahir','tahsin','talat','taner','tanju','tanzer','tarcan','tarik','tasin','tayfun','taylan','tayyar','tayyip','tekin',
            'temel','teoman','tevfik','tezcan','timur','timucin','tolga','tolgahan','toygar','tufan','tugay','tugrul','tuna','tuncay',
            'tunc','tuncel','tuncer','turab','turan','turgay','turgut','turker','tugkan','tugcan',
            'ufuk','ugur','ulas','ulvi','umit','unal','unsal','ural','uras','urcun','usame','utku','uygar','uygun',
            'vahap','vahit','vakkas','vasif','vatan','vecdi','vecihi','vedat','veli','veysel','veysi','vural',
            'yahya','yaman','yasar','yasin','yasir','yavuz','yener','yetkin','yigit','yildirim','yilmaz','yunus','yusuf','yuksel',
            'zafer','zahit','zekai','zekeriya','zeki','ziya','ziyaettin','zubeyir',
        ];
    }

    private static function kadinAdlari()
    {
        return [
            'afet','afitap','ahsen','akgul','aleyna','aliye','almira','alya','asena','asiye','asli','asuman',
            'ayben','ayca','aycan','ayda','aydan','aydanur','ayfer','aygul','aygun','aylin','aynur','aysu','aysun','ayse',
            'aysegul','aysen','aysenur','ayten',
            'banu','basak','bedia','behice','behiye','belkis','belgin','beliz','belma','benay','bengi','bengisu','bengu','beren',
            'berfin','berfu','berice','berin','berivan','berna','berrak','berrin','betul','beyhan','beyza','bezar','biket','binnaz',
            'binnur','birgul','birsen','birsel','buket','burcu','busra',
            'cana','canan','candan','canset','ceren','ceyda','ceylan','cigdem','cilvenaz',
            'defne','demet','derya','didem','dilan','dilara','dilay','dilek','dilfeza','dudu','duru','durusu','duygu',
            'ebrar','ebru','ece','eda','edibe','ela','elcin','elif','elifsu','eliz','elmas','elvan','emel','emine','emire',
            'emrenur','erva','ervanur','eser','esin','esma','esmer','esra','esmeray','esrasu','eylem','eylul','ezgi',
            'fadime','fatima','fatma','fatmagul','fazile','fazilet','ferda','ferdane','feride','feriha','feryal','fevziye','feyha',
            'feyza','fidan','figen','filiz','fitnat','fulden','fulin','fulya','funda','fusun',
            'gaye','gamze','gizem','gokce','gonca','gonul','gorsen','gulay','gul','gulbahar','gulcan','guler','gulnar','gulnaz','gulnur',
            'gulpembe','gulsah','gulsen','gulseren','gulsum','gunay','guzide',
            'hale','halime','hanife','hanim','hatice','havva','hayriye','hilal','huda','hulya','humeyra','hurriye',
            'icim','ilkay','ilknur','inci','ipek','irem','isil',
            'jale',
            'kader','kamile','kayra','kerime','kevser','kismet','kiymet','kubra',
            'lale','latife','leman','lerzan','leyla','lutfiye',
            'mahbube','makbule','mehtap','mehlika','melahat','melda','melek','melike','melisa','meltem','meneksenur','menekse','meral',
            'merve','meryem','mihriban','mine','miray','muge','munevver','munire','mukerrem','mujgan','muzeyyen',
            'naciye','naime','nazan','nazli','nazmiye','nebahat','necla','neslihan','nese','nesibe','nezahat','nezehat','nigar','nihal',
            'nihan','nilay','nilgun','nimet','nisa','nisanur','nuray','nurcan','nurdan','nuriye','nurseli','nursen','nurten',
            'oya','ozge','ozlem',
            'pakize','pelin','pelinsu','perihan','peri','pinar',
            'rabia','ravza','raziye','refika','remziye','reyhan','rojda','ruveyda','ruya',
            'sabahat','sabriye','sadiye','safiye','sakine','salime','samiye','sanem','sare','sarya','sati','satiye','saziye',
            'seba','sebahat','secil','seda','sedef','selcen','selda','selen','selin','selma','sema','semiha','semra','sena','sennur',
            'senanur','senay','sengul','serap','serfiraz','serna','serpil','sevcan','sevda','sevde','sevgi','sevil','sevilay','sevim',
            'sevinc','seyhan','sezen','sidika','sila','sibel','simay','simge','sinem','sirma','songul','sukran','sultan','suna','suzan',
            'suheyla','sumeyye','sumeyra',
            'taban','tahire','tansel','tansu','telli','tomris','tuana','tugba','tugce','tulay','tulin','turkan',
            'ulker','ummuhan',
            'vahide','vasfiye','vesile','vildan','vuslat',
            'yagmur','yasemin','yeliz','yildiz',
            'zahide','zarife','zehra','zekiye','zeliha','zerrin','zeynep','zuhre','ziynet','zubeyde',
        ];
    }
}
