<?php 
/*
Plugin Name: WP Russian Horoscope
Plugin URI: http://glaswr.ru/
Description: Плагин для отображения ежедневного гороскопа. С возможностью выбора дополнительной категории.
Version: 1.0
Author: Galswr
Author URI: http://glaswr.ru/
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

add_option('wp-ruhoroscope-old-date', 0);
add_option('wp-ruhoroscope-array-date', 0);
add_option('wp-ruhoroscope-array-type', array('общий' => 'bn', 'любовный' => 'love', 'мобильный' => 'mob', 'автомобильный' => 'auto', 'кулинарный' => 'cook'));
add_option('wp-ruhoroscope-array-mark', array('овен', 'телец', 'близнецы', 'рак', 'лев', 'дева', 'весы', 'скорпион', 'стрелец', 'козерог', 'водолей', 'рыбы'));
add_shortcode('ruhoroscope', 'shortcode_ruhoroscope');
add_action('admin_menu', 'register_index_ruhoroscope');

function register_index_ruhoroscope() {
	add_submenu_page('options-general.php', 'Гороскоп', 'Гороскоп', 'manage_options', 'ruhoroscope', 'index_ruhoroscope' ); 
}

if ( (get_option('wp-ruhoroscope-old-date') == 0 && get_option('wp-ruhoroscope-array-date') == 0) || (strtotime(date('Y-m-d')) > get_option('wp-ruhoroscope-old-date')))  {
	update_ruhoroscope();
}

function update_ruhoroscope() {
	foreach (get_option('wp-ruhoroscope-array-type') as $key_type => $value_type) {
		$page_array = json_decode(parse_xml('http://www.hyrax.ru/cgi-bin/'.$value_type.'_xml.cgi'), true);
		$pre_array = $page_array['channel']['item'];
		if ($key_type == 'общий') {unset($pre_array[0]); $pre_array = array_values($pre_array);};

		foreach ($pre_array as $key => $value) {
			$return_array[$key] = $value['description'];
		}

		$glob_array[$value_type] = $return_array;
	}

	update_option('wp-ruhoroscope-old-date', strtotime(date('Y-m-d')));
	update_option('wp-ruhoroscope-array-date', json_encode($glob_array));
}

function parse_xml($url) {
	$fileContents= file_get_contents($url);
	$fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
	$fileContents = trim(str_replace('"', "'", $fileContents));
	$simpleXml = simplexml_load_string($fileContents);
	$json = json_encode($simpleXml);

	return $json;
}


function shortcode_ruhoroscope($atts) {
	extract(shortcode_atts(array(
		"type" => 'общий',
		"mark" => 'овен'
		), $atts));

	$final_array = json_decode(get_option('wp-ruhoroscope-array-date'), true);
	$mark_array = get_option('wp-ruhoroscope-array-mark');
	$type_array = get_option('wp-ruhoroscope-array-type');

	$key_type = $type_array[mb_strtolower(rtrim(trim($type)))];
	$key_mark = array_search(mb_strtolower(rtrim(trim($mark))), $mark_array);

	return $final_array[$key_type][$key_mark];
}

function index_ruhoroscope() {
	echo '<div class="wrap">
	<h2 style="text-align:center;width: 574px;">Плагин WP Russian Horoscope by Glaswr</h2>
	<div class="card pressthis">
		<h3>Общая информация <span style="float:right">Гороскоп от: '.date("d-m-Y", get_option("wp-ruhoroscope-old-date")).'</span></h3>
		<p>Данный плагин отображает актуальный на текущую дату гороскоп по нескольким категориям. Которые вы можете регулировать и выводить информацию в шорткоде.</p>
		
		<h3>Категории</h3>
		<ul>
			<li><b>Общий</b> - общая характеристика дня основанная на лунном календаре и прогнозы для 12 знаков зодиака</li>
			<li><b>Любовный</b> - любовный гороскоп для 12 знаков зодиака</li>
			<li><b>Мобильный</b> - юмористический гороскоп для владельцев мобильных телефонов для 12 знаков зодиака</li>
			<li><b>Автомобильный</b> - юмористический гороскоп для владельцев автомобилей 12 знаков зодиака</li>
			<li><b>Кулинарный</b> - кулинарный гороскоп для 12 знаков зодиака</li>
		</ul>
		<h3>Структура шорткодов</h3>
		<p><code>[ruhoroscope type="%TYPE%" mark="%MARK%"]</code><br><br>
			<b>%TYPE%</b> - Тип гороскопа. Например: <i>Любовный</i> <br>
			<b>%MARK%</b> - Знак гороскопа. Например: <i>Бизнецы</i>
		</p>

		<h3>Генератор шорткодов</h3>
		<div style="text-align:center">
			<select class="postform" id="type" onchange="gen_code()">
				<option value="Общий" selected="selected">Общий</option>
				<option value="Любовный">Любовный</option>
				<option value="Мобильный">Мобильный</option>
				<option value="Автомобильный">Автомобильный</option>
				<option value="Кулинарный">Кулинарный</option>
			</select>

			<select class="postform" id="mark" onchange="gen_code()">
				<option value="Овен" selected="selected">Овен</option>
				<option value="Телец">Телец</option>
				<option value="Близнецы">Близнецы</option>
				<option value="Рак">Рак</option>
				<option value="Лев">Лев</option>
				<option value="Дева">Дева</option>
				<option value="Весы">Весы</option>
				<option value="Скорпион">Скорпион</option>
				<option value="Стрелец">Стрелец</option>
				<option value="Козерог">Козерог</option>
				<option value="Водолей">Водолей</option>
				<option value="Рыбы">Рыбы</option>
			</select>
			<br>
			<p id="code" style="padding-top:5px;"><code>[ruhoroscope type="Общий" mark="Овен"]</code></p>
		</div>
		<h3>Написать создателю</h3>
		Skype - <a href="skype:glaswr">Glaswr</a><br>
		Email - <a href="mailto:glaswr@yandex.ru">glaswr@yandex.ru</a><br>
		Blog - <a href="http://glaswr.ru/">Glaswr.Ru</a> <br>
		FL - <a href="https://www.fl.ru/users/Glaswr/">Glaswr.Ru</a> 

		<h3>Поддержать</h3>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
		<style> #wm-container {margin: 0 auto;width: 420px; margin-top: 15px;}</style>
		<iframe frameborder="0" allowtransparency="true" scrolling="no" src="https://money.yandex.ru/embed/donate.xml?account=41001473206537&quickpay=donate&payment-type-choice=off&default-sum=100&targets=%D0%9F%D0%BE%D0%B4%D0%B4%D0%B5%D1%80%D0%B6%D0%BA%D0%B0+%D0%B2+%D1%81%D0%BE%D0%B7%D0%B4%D0%B0%D0%BD%D0%B8%D0%B8+%D0%BF%D0%BB%D0%B0%D0%B3%D0%B8%D0%BD%D0%BE%D0%B2&target-visibility=on&project-name=&project-site=http%3A%2F%2Fglaswr.ru%2F&button-text=01&successURL=" width="525" height="104"></iframe>	
		<script src="//merchant.webmoney.ru/conf/lib/wm-simple-x20.min.js?wmid=139705418512&purse=R362689698766&key=302227911&amount=100.00&desc=%CF%EE%E4%E4%E5%F0%E6%EA%E0+%E2+%F1%EE%E7%E4%E0%ED%E8%E8+%EF%EB%E0%E3%E8%ED%EE%E2" id="wm-script"></script>
		
		<script>
			function gen_code() {
				var type, mark;
				type = document.getElementById("type").options[document.getElementById("type").selectedIndex].text;
				mark = document.getElementById("mark").options[document.getElementById("mark").selectedIndex].text;
				document.getElementById("code").innerHTML="<code>[ruhoroscope type=\""+type+"\" mark=\""+mark+"\"]</code>";
			}
		</script>
	</div>
</div>';
}

?>