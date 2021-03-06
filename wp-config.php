<?php
/**
 * As configurações básicas do WordPress
 *
 * O script de criação wp-config.php usa esse arquivo durante a instalação.
 * Você não precisa usar o site, você pode copiar este arquivo
 * para "wp-config.php" e preencher os valores.
 *
 * Este arquivo contém as seguintes configurações:
 *
 * * Configurações do MySQL
 * * Chaves secretas
 * * Prefixo do banco de dados
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/pt-br:Editando_wp-config.php
 *
 * @package WordPress
 */

// ** Configurações do MySQL - Você pode pegar estas informações com o serviço de hospedagem ** //
/** O nome do banco de dados do WordPress */
define('WP_SITEURL','http://minhapizzariateste-com-br.umbler.net');
define('WP_HOME','http://minhapizzariateste-com-br.umbler.net');
define('DB_NAME', 'minhapizzariates');

/** Usuário do banco de dados MySQL */
define('DB_USER', 'minhapizzariates');

/** Senha do banco de dados MySQL */
define('DB_PASSWORD', 'senha123');

/** Nome do host do MySQL */
define('DB_HOST', 'mysql942.umbler.com');

/** Charset do banco de dados a ser usado na criação das tabelas. */
define('DB_CHARSET', 'utf8mb4');

/** O tipo de Collate do banco de dados. Não altere isso se tiver dúvidas. */
define('DB_COLLATE', '');

/**#@+
 * Chaves únicas de autenticação e salts.
 *
 * Altere cada chave para um frase única!
 * Você pode gerá-las
 * usando o {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org
 * secret-key service}
 * Você pode alterá-las a qualquer momento para invalidar quaisquer
 * cookies existentes. Isto irá forçar todos os
 * usuários a fazerem login novamente.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '# K]]=Jq#6l:fjKiH*tIBc,JK{%._>%xPhs(W6dHy>WSxZ:)E+Mi67gw{g{rSkM{');
define('SECURE_AUTH_KEY',  '1p0gk}En82uW;p?.D21 r03`m/:C`,z6wc^L;mMJh& D1ma?W a# tCerIfdJ/hV');
define('LOGGED_IN_KEY',    'RnZj4:A~Md}J96+%uBiR0P`X~i/YAhsD*6u*@/}1P)6^aXAcIl=L}xt<<DS=NykZ');
define('NONCE_KEY',        'UO7:<e~;UV B(oM}AMS/AIFhHclr>902,m,cj4f221Y:`P8t09oNTR5@wN9I9%|$');
define('AUTH_SALT',        'Uh+tPQyf?x}]n$`r8U<Wc^sNS?9C)r[fntNbA3&<.0*OaHTs~8r+UUrr#*8b*w9p');
define('SECURE_AUTH_SALT', 'x&,3&rJ5]&kiHMn>hwU9/*OAh~OhO[Vn6k,}5`@.DFvVp-C!R]&7fcDTv@y#!o+Y');
define('LOGGED_IN_SALT',   '}uVPaQ4ci%Tg -6QM3V1!Hf|!2pzc,f?^ha2EC/.=n_.{%zAn>o!*U>WpY.dR6`*');
define('NONCE_SALT',       'iMigX~$?sjeuc2yln{sC`,M*2,[3@tyW`rKC0AAPl%A{~6F](_mRfM.VJxUBf[UM');

/**#@-*/

/**
 * Prefixo da tabela do banco de dados do WordPress.
 *
 * Você pode ter várias instalações em um único banco de dados se você der
 * um prefixo único para cada um. Somente números, letras e sublinhados!
 */
$table_prefix  = 'pizz_';

/**
 * Para desenvolvedores: Modo de debug do WordPress.
 *
 * Altere isto para true para ativar a exibição de avisos
 * durante o desenvolvimento. É altamente recomendável que os
 * desenvolvedores de plugins e temas usem o WP_DEBUG
 * em seus ambientes de desenvolvimento.
 *
 * Para informações sobre outras constantes que podem ser utilizadas
 * para depuração, visite o Codex.
 *
 * @link https://codex.wordpress.org/pt-br:Depura%C3%A7%C3%A3o_no_WordPress
 */
define('WP_DEBUG', false);

/* Isto é tudo, pode parar de editar! :) */

/** Caminho absoluto para o diretório WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Configura as variáveis e arquivos do WordPress. */
require_once(ABSPATH . 'wp-settings.php');
