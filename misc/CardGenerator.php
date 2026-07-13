<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//include_once('../Artists/artists.inc.php');
//include_once('../Flavor/flavors.inc.php');
//include_once('starters.inc.php');
//echo count(ARTISTS);

function varexport($expression, $return = FALSE)
{
    $export = var_export($expression, TRUE);
    $patterns = [
        "/array \(/" => '[',
        "/^([ ]*)\)(,?)$/m" => '$1]$2',
        "/=>[ ]?\n[ ]+\[/" => '=> [',
        "/([ ]*)(\'[^\']+\') => ([\[\'])/" => '$1$2 => $3',
    ];
    $export = preg_replace(array_keys($patterns), array_values($patterns), $export);
    if ((bool)$return) return $export;
    else echo $export;
}


function slugify($text)
{
    // replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '', $text);

    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    // trim
    $text = trim($text, '');

    // lowercase
    //  $text = mb_strtolower($text);

    if (empty($text)) {
        return 'n-a';
    }

    return $text;
}

function replaceBracket(&$str)
{
    $str = str_replace("[", "<", $str);
    $str = str_replace("]", ">", $str);
}

$o = 0;
$i = 0;
if (($handle = fopen("tiles.csv", "r")) !== FALSE) {
    while (($row = fgetcsv($handle, 2000, ";")) !== FALSE && $i++ <= 600) {
        if ($i <= 1) {
            continue;
        }
        //var_dump($row);

        $id = $row[0];
        $type = ucfirst($row[1]);
        $name = ucwords($row[2]);
        $strength = $row[3];

        $categories = [];
        $continents = [];
        $openAreas = [];
        $gender = null;
        $effect = '';
        $appeal = 0;

        if ($row[4] != '') {
            $categories[] = strtoupper($row[4]);
        }
        if ($row[6] != '') {
            $categories[] = strtoupper($row[6]);
        }
        if ($row[5] != '') {
            $continents[] = strtoupper($row[5]);
        }

        if ($row[7] != '') {
            $open = explode(',', trim($row[7]));
            foreach ($open as $area) {
                $openAreas[] = trim($area);
            }
        }

        if ($row[8] != '') {
            $gender = strtoupper(substr($row[8], 0, 1));
        }

        if ($row[9] != '') {
            $effect = "immediate " . $row[9];
        }
        if ($row[10] != "") {
            $effect .= "####ongoing" . $row[10];
        }
        if ($row[11] != "") {
            $effect .= "#####prerequisite " . $row[11];
        }

        if ($row[12] != "") {
            $appeal = $row[12];
        }


        $uId = strtoupper(substr($type, 0, 1)) . str_pad($id, 3, '0', STR_PAD_LEFT) . "_" . slugify($name) . "_" . (!is_null($gender) ? $gender : 'N');


        echo "	$uId,\n";
        //		continue;




        @mkdir($type);

        $fp = fopen("$type/$uId.php", 'w');
        // print($className);
        fwrite($fp, "<?php
namespace Bga\\Games\\Sanctuary\\Tiles\\$type" . "s;
use Bga\Games\Sanctuary\Constants\Icons;

class " . $uId . " extends \Bga\Games\Sanctuary\Models\\$type
{
  public function __construct(\$row){
		parent::__construct(\$row);
       \$this->id = '$uId';
       \$this->name = '" . strtoupper($name) . "';
       \$this->appeal = " . (is_int($appeal) ? $appeal : "'$appeal'") . ";
       \$this->strength = $strength;
       \$this->gender = " . (!is_null($gender) ? "'$gender'" : "'N'") . ";
       //effect = '$effect';
  ");

        if (!empty($categories)) {
            fwrite($fp, "     \$this->categories = [Icons::" . join(",Icons::", $categories) . "]; \n");
        }
        if (!empty($continents)) {
            fwrite($fp, "     \$this->continents = [Icons::" . join(",Icons::", $continents) . "]; \n");
        }
        if (!empty($openAreas)) {
            fwrite($fp, "     \$this->openAreas = ['" . join("','", $openAreas) . "']; \n");
        }
        if ($gender == 'M') {
            $gender = 'F';
            $pair = strtoupper(substr($type, 0, 1)) . str_pad($row[13], 3, '0', STR_PAD_LEFT) . "_" . slugify($name) . "_" . (!is_null($gender) ? $gender : 'N');
            fwrite($fp, "     \$this->pair = '$pair'; \n");
        } elseif ($gender == 'F') {
            $gender = 'M';
            $pair = strtoupper(substr($type, 0, 1)) . str_pad($row[13], 3, '0', STR_PAD_LEFT) . "_" . slugify($name) . "_" . (!is_null($gender) ? $gender : 'N');
            fwrite($fp, "     \$this->pair = '$pair'; \n");
        }

        fwrite($fp, "
  }
}
");


        fclose($fp);
    }
    fclose($handle);
}
