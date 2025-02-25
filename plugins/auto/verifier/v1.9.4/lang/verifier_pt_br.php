<?php
// This is a SPIP language file  --  Ceci est un fichier langue de SPIP
// extrait automatiquement de https://trad.spip.net/tradlang_module/verifier?lang_cible=pt_br
// ** ne pas modifier le fichier **

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

$GLOBALS[$GLOBALS['idx_lang']] = array(

	// E
	'erreur_code_postal' => 'Este Cep está incorreto.',
	'erreur_comparaison_egal' => 'O valor deve ser igual ao campo "@nom_champ@"', # MODIF
	'erreur_comparaison_egal_type' => 'O valor deve ser igual e do mesmo tipo que o campo "@nom_champ@"', # MODIF
	'erreur_comparaison_grand' => 'O valor deve ser superior ao campo "@nom_champ@"', # MODIF
	'erreur_comparaison_grand_egal' => 'O valor deve ser superior ou igual ao campo "@nom_champ@"', # MODIF
	'erreur_comparaison_petit' => 'O valor deve ser inferior ao campo "@nom_champ@"', # MODIF
	'erreur_comparaison_petit_egal' => 'O valor deve ser inferior ou igual ao campo "@nom_champ@"', # MODIF
	'erreur_couleur' => 'O código de cor não é válido.',
	'erreur_date' => 'A data não é válida.',
	'erreur_date_format' => 'O formato da data não é aceitável.',
	'erreur_decimal' => 'O valor deve ser um número decimal.',
	'erreur_decimal_nb_decimales' => 'O número não deve ter mais de @nb_decimales@ algarismos depois da vírgula.',
	'erreur_email' => 'O endereço de e-mail <em>@email@</em> não está num formato válido.',
	'erreur_email_nondispo' => 'O endereço de e-mail <em>@email@</em> já está sendo usado.',
	'erreur_entier' => 'O valor deve ser um número inteiro.',
	'erreur_entier_entre' => 'O valor dever ser entre @min@ e @max@.',
	'erreur_entier_max' => 'O valor deve ser inferior a @max@.', # MODIF
	'erreur_entier_min' => 'O valor deve ser superior a @min@.', # MODIF
	'erreur_heure' => 'O horário indicado não existe.',
	'erreur_heure_format' => 'O formato de hora não é aceitável.',
	'erreur_id_document' => 'Esta extensão de documento não é válida.',
	'erreur_id_objet' => 'Este ID não é válido.',
	'erreur_inconnue_generique' => 'O formato não está correto.',
	'erreur_isbn' => 'O número ISBN não é válido (ex: 978-2-1234-5680-3 ou 2-1234-5680-X)', # MODIF
	'erreur_isbn_13_X' => 'Um número ISBN-13 não pode terminar em X.',
	'erreur_isbn_G' => 'O primeiro segmento deve ser igual a 978 ou 979.',
	'erreur_isbn_nb_caracteres' => 'O número ISBN deve ter 10 ou 13 caractéres, sem contar os traços (atualmente @nb@).',
	'erreur_isbn_nb_segments' => 'O número ISBN deve ter 4 ou 5 segmentos (atualmente @nb@).',
	'erreur_isbn_segment' => 'O segmento "@segment@" tem @nb@ número(s) a mais.',
	'erreur_isbn_segment_lettre' => 'O segmento "@segment@" não pode conter letras.',
	'erreur_numerique' => 'O formato do número não é válido.',
	'erreur_objet' => 'Este objeto não é válido', # MODIF
	'erreur_regex' => 'O formato da expressão não é válido.',
	'erreur_siren' => 'O número SIREN não é válido.',
	'erreur_siret' => 'O número SIRET não é válido.',
	'erreur_taille_egal' => 'O valor deve ter exatamente @egal@ caracteres (atualmente @nb@).',
	'erreur_taille_entre' => 'O valor deve ter entre @min@ e @max@ caracteres (atualmente @nb@).',
	'erreur_taille_max' => 'O valor deve ter no máximo @max@ caracteres (atualmente @nb@).',
	'erreur_taille_min' => 'O valor deve ter no mínimo @min@ caracteres (atualmente @nb@).',
	'erreur_telephone' => 'O número não é válido.',
	'erreur_url' => 'O endereço <em>@url@</em> não é válido.',
	'erreur_url_protocole' => 'O endereço informado <em>(@url@)</em> deve começar com @protocole@', # MODIF
	'erreur_url_protocole_exact' => 'O endereço informado <em>(@url@)</em> não começa com um protocolo válido (http:// por exemplo)', # MODIF

	// N
	'normaliser_option_date' => 'Normalizar a data?',
	'normaliser_option_date_aucune' => 'Não',
	'normaliser_option_date_en_datetime' => 'No formato «Datetime» (do SQL)',

	// O
	'option_code_postal_pays_explication' => 'Código de país de dois caracteres: BR, PT, FR etc.',
	'option_code_postal_pays_label' => 'País',
	'option_comparaison_champ_champ_explication' => 'Identificador do campo (atributo « nome »)',
	'option_comparaison_champ_champ_label' => 'Campo',
	'option_comparaison_champ_comparaison_explication' => 'Tipo de comparação a efetuar',
	'option_comparaison_champ_comparaison_label' => 'Comparação',
	'option_comparaison_champ_egal' => '== Igual',
	'option_comparaison_champ_egal_type' => '=== Idêntico (mesmo tipo)',
	'option_comparaison_champ_grand' => '> Maior',
	'option_comparaison_champ_grand_egal' => '>= Maior ou igual',
	'option_comparaison_champ_nom_champ_explication' => 'Nome do campo para humanos',
	'option_comparaison_champ_nom_champ_label' => 'Nome do campo',
	'option_comparaison_champ_petit' => '< Menor',
	'option_comparaison_champ_petit_egal' => '<= Menor ou igual',
	'option_couleur_normaliser_label' => 'Normalizar o código de cor?',
	'option_couleur_type_hexa' => 'Código de cor no formato hexadecimal',
	'option_couleur_type_label' => 'Tipo de verificação a efetuar',
	'option_decimal_nb_decimales_label' => 'Número de décimais depois da vírgula', # MODIF
	'option_email_disponible_label' => 'E-mail disponível',
	'option_email_disponible_label_case' => 'Verificar se o e-mail já está sendo utilizado por um usuário',
	'option_email_mode_5322' => 'A verificação mais de acordo com os padrões disponíveis',
	'option_email_mode_label' => 'Modo de verificação de e-mails',
	'option_email_mode_normal' => 'Verificação normal do SPIP',
	'option_email_mode_strict' => 'Verificação menos permissiva',
	'option_entier_max_label' => 'Valor máximo',
	'option_entier_min_label' => 'Valor mínimo',
	'option_regex_modele_label' => 'O valor deve correspponder à máscara a seguir',
	'option_siren_siret_mode_label' => 'O que você quer verificar?',
	'option_siren_siret_mode_siren' => 'O número SIREN',
	'option_siren_siret_mode_siret' => 'O número SIRET',
	'option_taille_max_label' => 'Tamanho máximo',
	'option_taille_min_label' => 'Tamanho mínimo',
	'option_url_mode_complet' => 'Verificação completa do URL',
	'option_url_mode_label' => 'Modo de verificação dos URLs',
	'option_url_mode_php_filter' => 'Verificação completa do URL através do filtro FILTER_VALIDATE_URL do php',
	'option_url_mode_protocole_seul' => 'Verificação apenas da presença de um protocolo',
	'option_url_protocole_label' => 'Nome do protocolo a ser verificado',
	'option_url_type_protocole_exact' => 'Informar um protocolo abaixo:',
	'option_url_type_protocole_ftp' => 'Protocolos ftp: ftp ou sftp',
	'option_url_type_protocole_label' => 'Tipo de protocolo a ser verificado',
	'option_url_type_protocole_mail' => 'Protocolos de e-mail: imap, pop3 ou smtp',
	'option_url_type_protocole_tous' => 'Todos os protocolos aceitos',
	'option_url_type_protocole_web' => 'Protocolos web: http ou https',

	// T
	'type_code_postal' => 'CEP',
	'type_code_postal_description' => 'Verifica se o valor é um CEP válido',
	'type_comparaison_champ' => 'Comparação',
	'type_comparaison_champ_description' => 'Compara o valor com um outro campo do _request().',
	'type_couleur' => 'Cor',
	'type_couleur_description' => 'Verifica se o valor é um código de cor.',
	'type_date' => 'Data',
	'type_date_description' => 'Verifica se o valor é uma data no formato DD/MM/AAAA. O separador é livre (".", "/" etc).',
	'type_decimal' => 'Número decimal',
	'type_decimal_description' => 'Verifica se o valor é um número decimal, com a possibilidade de restringir entre dois valores e de especificar o número de decimais após a vírgula.',
	'type_email' => 'Endereço de e-mail',
	'type_email_description' => 'Verifica se o endereço de e-mail está num formato correto.',
	'type_email_disponible' => 'Disponibilidade de um endereço de e-mail',
	'type_email_disponible_description' => 'Verifica se o endereço de e-mail já está sendo utilizado por outro usuário do sistema.',
	'type_entier' => 'Número inteiro',
	'type_entier_description' => 'Verifica se o valor é um número inteiro, com a possibilidade de restringir entre dois valores.',
	'type_id_document' => 'Número de um documento',
	'type_id_document_description' => 'Verifica se o valor corresponde a um número de documento existente.',
	'type_isbn' => 'Número ISBN',
	'type_isbn_description' => 'Verifica se o valor corresponde a um número ISBN de 10 ou 13 dígitos.',
	'type_regex' => 'Expressão regular',
	'type_regex_description' => 'Verifica se o valor corresponde à máscara solicitada. Para a utilização de máscaras, consulte <a href="http://fr2.php.net/manual/fr/reference.pcre.pattern.syntax.php">ajuda online do PHP</a>.',
	'type_siren_siret' => 'SIREN ou SIRET',
	'type_siren_siret_description' => 'Verifica se o valor é um número válido de <a href="http://fr.wikipedia.org/wiki/SIREN">sistema de identificação do cadastro de empresas (Système d’Identification du Répertoire des ENtreprises)</a> francês.',
	'type_taille' => 'Tamanho',
	'type_taille_description' => 'Verifica se o tamanho do valor corresponde ao mínimo e/ou ao máximo solicitado.',
	'type_telephone' => 'Número de telefone',
	'type_telephone_description' => 'Verifica se o número de telefone corresponde a um esquema reconhecido.',
	'type_url' => 'URL',
	'type_url_description' => 'Verifica se o URL corresponde a um esquema reconhecido.'
);
