<?php
// JWT
require_once(__DIR__ . "/../../public/base/plugins/php-jwt/src/JWT.php");
require_once(__DIR__ . "/../../public/base/plugins/php-jwt/src/ExpiredException.php");

use \Firebase\JWT\JWT;

// Inicia a sessão
session_start();

// Transforma objeto em array
function objectToArray($d)
{
	if (is_object($d)) {
		$d = get_object_vars($d);
	}
	if (is_array($d)) {
		return array_map(__FUNCTION__, $d);
	} else {
		return $d;
	}
}

// Função verifica a passagem de usuário via parâmetro
// e decodifica o JWT.
function getUserFromVCardapio()
{
	$query = explode("&", explode("?", $_SERVER['REQUEST_URI'])[1]);
	$user = explode("=", $query[0])[1];

	if (empty($user)) return null;
	$key = "fa7a8bb90e1a6b460217";

	try {
		$decoded = JWT::decode($user, $key, array('HS256'));
	} catch(\Exception $e) {
		return null;
	}

	if (!empty($decoded) && !empty($decoded->user))
		return objectToArray($decoded->user); // Converte para Array
	return null;
}
$user =	getUserFromVCardapio();

// Variável que verifica se o usuário está logado
if (!isset($_SESSION['PL_USER']) && empty($user)) {
	$_SESSION['PL_USER'] = false;
} else if (!empty($user)) {
	$_SESSION['PL_USER'] = $user;
}

// Erro do login
$_SESSION['login_erro'] = false;

// session_destroy();

//ARQUIVO DE CONFIGURACAO LOJA
// Verifica se existe um slug salvo na sessão
// para acessar o arquivo de configurações.
if (isset($_SESSION['PL_USER']) && !empty($_SESSION['PL_USER']['slug'])) {
	$nome_arquivo = [$_SESSION['PL_USER']['slug']];
} else {
	$nome_arquivo = $_SERVER['HTTP_HOST'];
	$nome_arquivo = str_replace('www.', '', $nome_arquivo);
	$nome_arquivo = explode('.', $nome_arquivo);
}

$diretorio    = realpath(__DIR__ . '/..');
$arquivo      = fopen($diretorio . '/private/users/' . $nome_arquivo[0] . '.txt', 'r');

$conteudo_arquivo = fgets($arquivo, 1024);

// Fecha arquivo aberto
fclose($arquivo);

$dados_arquivo = explode('&', $conteudo_arquivo);

$dados_loja = explode(';', $dados_arquivo[0]);
$dados_bd = explode(';', $dados_arquivo[1]);

define('HOST_SERVER', $dados_bd[0]);
define('USER_SERVER', $dados_bd[1]);
define('PASS_SERVER', $dados_bd[2]);
define('DB_SERVER', $dados_bd[3]);

include 'dataBase.php';
include 'classAdmin.php';
include 'pagination.php';

$bd = new Banco();
$pagination = new Pagination();

ini_set('display_errors', true);
error_reporting(E_ALL);

//setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');

$sqli = $bd->conexao();
$query = "SELECT navegacao_segura FROM design";

$navegacao_segura = $sqli->query($query);
$navegacao_segura = $navegacao_segura->fetch_array();

if ($navegacao_segura['navegacao_segura'] == 1) {
	$protocolo = 'https://';
} else {
	$protocolo = 'http://';
}

// $protocolo = $_SERVER['REQUEST_SCHEME'];
// $protocolo = $_SERVER['HTTP_X_FORWARDED_PROTO'];

define('NOME_LOJA', $dados_loja[0]);
define('EMAIL_LOJA', $dados_loja[1]);
define('PASTA_UPLOAD', $nome_arquivo[0]);
define('PROJECT', 'painel');
define('PL_BASE', $protocolo . $_SERVER['HTTP_HOST'] . '/');
define('PL_PATH', realpath(__DIR__ . '/..'));
define('PL_PATH_ADMIN', PL_BASE . PROJECT);
// define('PL_PATH_ADMIN', PL_BASE);
define('PL_PATH_CLASS', PL_PATH_ADMIN . '/content/class');
define('PL_PATH_IMAGES_UPLOAD', PL_PATH_ADMIN . '/uploads/' . PASTA_UPLOAD . '/');


function limpaString($texto)
{
	$aFind = array('&', 'á', 'à', 'ã', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ü', 'ç', 'Á', 'À', 'Ã', 'Â', 'É', 'Ê', 'Í', 'Ó', 'Ô', 'Õ', 'Ú', 'Ü', 'Ç', '!', '`', '?', '.', '@', '#', '$', '%', '"', '*', '(', ')', '+', '=', '§', 'ª', '[', ']', '{', '}', 'º', '/', '°', ';', ':', '>', '<', ',', '¹', '²', '³', '£', '¢', '¬', '~', '^', '|', '_', ' ', '–', "'", "”");
	$aSubs = array('e', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'c', 'A', 'A', 'A', 'A', 'E', 'E', 'I', 'O', 'O', 'O', 'U', 'U', 'C', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '', '');
	$novoTexto = str_replace($aFind, $aSubs, $texto);
	//$novoTexto = preg_replace("/[^a-zA-Z0-9 @,-.;:]/", "", $novoTexto);
	return $novoTexto;
} //fim limpaString

function limpaStringUnderLine($texto)
{
	$aFind = array('&', 'á', 'à', 'ã', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ü', 'ç', 'Á', 'À', 'Ã', 'Â', 'É', 'Ê', 'Í', 'Ó', 'Ô', 'Õ', 'Ú', 'Ü', 'Ç', '!', '`', '?', '.', '@', '#', '$', '%', '"', '*', '(', ')', '+', '=', '§', 'ª', '[', ']', '{', '}', 'º', '/', '°', ';', ':', '>', '<', ',', '¹', '²', '³', '£', '¢', '¬', '~', '^', '|', '-', ' ', "'", '”');
	$aSubs = array('e', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'c', 'A', 'A', 'A', 'A', 'E', 'E', 'I', 'O', 'O', 'O', 'U', 'U', 'C', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '', '');
	$novoTexto = str_replace($aFind, $aSubs, $texto);
	//$novoTexto = preg_replace("/[^a-zA-Z0-9 @,-.;:]/", "", $novoTexto);
	return $novoTexto;
} //fim limpaString

function pre($array)
{
	echo "<pre>";
	print_r($array);
	echo "</pre>";
}

function gera_url($url = '', $tabela = '', $id = '')
{
	$bd = new Banco();

	if (!empty($id)) {
		$where = array(
			'url = "' . $url . '"',
			'id = ' . $id
		);
		$dados = $bd->select(array('url'), $tabela, $where);
	} else {
		$where = array('url = "' . $url . '"');
		$dados = $bd->select(array('url'), $tabela, $where);
	}

	if (($dados->num_rows != 0) && (empty($id))) {
		$lmin = 'abcdefghijklmnopqrstuvwxyz';
		$lmai = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$num  = '1234567890';
		$retorno = '';
		$caracteres = '';

		$caracteres .= $lmin;
		$caracteres .= $lmai;
		$caracteres .= $num;

		$len = strlen($caracteres);

		for ($n = 1; $n <= 3; $n++) {
			$rand = mt_rand(1, $len);
			$retorno .= $caracteres[$rand - 1];
		}

		return $url . '-' . $retorno;
	} else {
		return $url;
	}
}

function convert_data($datestr = '')
{
	if ($datestr == '')
		return '';

	$datestr = date("d/m/Y", strtotime(implode("-", array_reverse(explode("/", $datestr)))));
	return date($datestr);
}

function convert_data_hora($datestr = '')
{
	if ($datestr == '')
		return '';

	$hour = explode(' ', $datestr);

	$datestr = date("d/m/Y", strtotime(implode("-", array_reverse(explode("/", $datestr)))));
	return date($datestr . ' ' . $hour[1]);
}

function datetime_bd($datestr = '')
{
	if ($datestr == '')
		return '';

	$datestr = date("Y-m-d", strtotime(implode("-", array_reverse(explode("/", $datestr)))));
	return date($datestr);
}

function convert_dayMonth($datestr = '')
{
	if ($datestr == '')
		return '';

	$datestr = date("d/m", strtotime(implode("-", array_reverse(explode("/", $datestr)))));
	return date($datestr);
}

function fileExists($filePath)
{
	return file_exists(PL_PATH . '/uploads/' . PASTA_UPLOAD . $filePath);
}

function verifica_status_loja($sqli)
{

	// $sqli = $bd->conexao();

	$query = "SELECT status_loja, data_criacao, data_vencimento  FROM faturas_configuracao";

	$faturas_configuracao = $sqli->query($query);
	$faturas_configuracao = $faturas_configuracao->fetch_array();

	$msg_retorno = '';
	//PERIODO DE TESTES
	if ($faturas_configuracao['status_loja'] == 3) {

		if (($faturas_configuracao['data_vencimento'] != '0000-00-00') && (!empty($faturas_configuracao['data_vencimento']))) {

			//dia da contratação
			$date = new DateTime($faturas_configuracao['data_criacao']);

			//dia atual
			$today = new DateTime(date('Y-m-d'));

			$diferenca = $today->diff($date);
			$dias = 20 - (int) $diferenca->days;

			if ($dias == 1) {

				$msg_retorno .= '<div class="alert alert-warning alert-dismissible">';
				$msg_retorno .= '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
				$msg_retorno .= '<h4>Período de teste!</h4>';
				$msg_retorno .= '<p>Seu período de teste encerra hoje! <a href="' . PL_PATH_ADMIN . '/faturas">Clique aqui</a> para verificar sua primeira fatura.</p>';
				$msg_retorno .= '</div>';
				$msg_retorno .= '<div style="clear: both;"></div>';

				// } else if(($dias > 0) && ($dias != 1)){
			} else if (($dias > 14) && ($dias != 1)) {

				$msg_retorno .= '<div class="alert alert-warning alert-dismissible">';
				$msg_retorno .= '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
				$msg_retorno .= '<h4>Período de teste!</h4>';
				$msg_retorno .= '<p>Faltam ' . $dias . ' dias para o final do período de testes! <a href="' . PL_PATH_ADMIN . '/faturas">Clique aqui</a> para verificar sua primeira fatura.</p>';
				$msg_retorno .= '</div>';
				$msg_retorno .= '<div style="clear: both;"></div>';
			} else {

				$msg_retorno .= '<div class="alert alert-danger alert-dismissible">';
				$msg_retorno .= '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
				$msg_retorno .= '<h4>Período de teste!</h4>';
				$msg_retorno .= 'O período de testes da sua loja encerrou! Evite o bloqueio, <a href="' . PL_PATH_ADMIN . '/faturas">clique aqui</a> e veja sua fatura.</p> ';
				$msg_retorno .= '</div>';
				$msg_retorno .= '<div style="clear: both;"></div>';
			}
		}
	} else if ($faturas_configuracao['status_loja'] == 1) {

		//FATURA EM ABERTO PARA PAGAMENTO
		$fatura = "SELECT * FROM faturas where status = 1";

		$fatura = $sqli->query($fatura);
		$fatura = $fatura->fetch_array();

		//dia atual
		$today = new DateTime(date('Y-m-d'));

		$fatura_vencendo = 0;

		if (($faturas_configuracao['data_vencimento'] != '0000-00-00') && (!empty($faturas_configuracao['data_vencimento']))) {

			if ($fatura != NULL) {

				$data_vencimento = new DateTime($fatura['data_vencimento']);

				$vencimento_fatura = $data_vencimento->format('d') - $today->format('d');

				//fatura não venceu ainda
				if ($vencimento_fatura > 0) {

					$vencimento = $data_vencimento->format('d') - $today->format('d');

					if ($vencimento < 5) {

						$msg_retorno .= '<div class="alert alert-warning alert-dismissible">';
						$msg_retorno .= '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
						$msg_retorno .= '<h4>Vencimento da fatura!</h4>';
						$msg_retorno .= '<p>Sua fatura vence em ' . $vencimento . ' dia(s)! <a href="' . PL_PATH_ADMIN . '/faturas">Clique aqui</a> para verificar a fatura.</p>';
						$msg_retorno .= '</div>';
					}

					//dia de vencimento da fatura
				} else if ($vencimento_fatura == 0) {

					$msg_retorno .= '<div class="alert alert-warning alert-dismissible">';
					$msg_retorno .= '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
					$msg_retorno .= '<h4>Vencimento da fatura!</h4>';
					$msg_retorno .= '<p>Sua fatura vence hoje! Verifique suas faturas <a href="' . PL_PATH_ADMIN . '/faturas">clicando aqui</a>.</p>';
					$msg_retorno .= '</div>';

					//fatura vencida
				} else if ($vencimento_fatura < 0) {

					$vencimento = $data_vencimento->format('d') - $today->format('d');

					$msg_retorno .= '<div class="alert alert-danger alert-dismissible">';
					$msg_retorno .= '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
					$msg_retorno .= '<h4>Sua fatura está vencida!</h4>';
					$msg_retorno .= '<p>Sua fatura está vencida! <a href="' . PL_PATH_ADMIN . '/faturas">Clique aqui</a> para verificar e evitar o bloqueio de sua loja.</p></p>';
					$msg_retorno .= '</div>';
				}
			}
		}
	}

	return $msg_retorno;
}
