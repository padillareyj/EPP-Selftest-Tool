#!/usr/bin/php
<?php
# Copyright (c) 2013 .SE (The Internet Infrastructure Foundation).
# All rights reserved.
#
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions
# are met:
# 1. Redistributions of source code must retain the above copyright
# notice, this list of conditions and the following disclaimer.
# 2. Redistributions in binary form must reproduce the above copyright
# notice, this list of conditions and the following disclaimer in the
# documentation and/or other materials provided with the distribution.
#
# THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
# IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
# WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
# ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY
# DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
# DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
# GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
# INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER
# IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
# OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN
# IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
#
# Written by Jan Saell <jan@irial.com>

// IREG .SE PDT backend logic class

print "EppDomCreate03 - test Create domain to tld epp server with dnssec\n";

require_once('./lib/parseIniFile.php');
require_once('./lib/ireg.php');
require_once('./lib/ireg_obj.php');
require_once('./lib/ireg_lib.php');

// Parse argument
$args = getopt('c:h:p:st:');
foreach (array('c', 'h', 'p', 't') as $opt) {
	if (!array_key_exists($opt, $args)) {
		die("usage: pdtConnTest -c config_file -h epphost -p eppport [-s] -t timeout\n");
	}
}

$CONF_FILE = $args['c'];
if (file_exists($CONF_FILE)) {
	$config = parseIniFile($CONF_FILE, true);
} else {
	die ("Cant open ini file '".$CONF_FILE."'\n");
}

$registry = "tld";

$init = array(
	$registry => array(
		'epphost' => $args['h'],
		'eppport' => $args['p'],
		'epptimeout' => $args['t']
	)
);
if (array_key_exists('s', $args)) {
	$init[$registry]['eppssl'] = 'true';
	if (isset($config['EppConnTest']['EppSNIServerName'])) {
		$init[$registry]['eppsniname'] = $config['EppConnTest']['EppSNIServerName'];
	}
} else {
	$init[$registry]['eppssl'] = 'false';
}

$registry	= "tld";
$phase		= "EppDomCreate03";

if (!isset($init[$registry])) {
	print $argv[0]." missing registry '".$registry."'\n";
	exit (2);
}
$EPP_USER	= $config['EppConnTest']['EppLoginId'];
$EPP_PWD	= $config['EppConnTest']['EppLoginPwd'];
$EPP_CERT_PATH	= $config['EppConnTest']['EppClientKeyPairPem'];
$EPP_CERT_PWD	= $config['EppConnTest']['EppClientKeyPairPwd'];

$con = new ireg($config, $phase, $EPP_USER, $EPP_PWD, $EPP_CERT_PATH, $EPP_CERT_PWD, $init);
ireg_epp_log_start($registry, $con);
//$con->debugOn("/tmp/js.txt");

$exCode=-1;

$domain = $config[$phase][$phase.'Name'];
$period = strtolower($config[$phase][$phase.'Period']);
$pno = $config[$phase][$phase.'PeriodValue'];
$registrant = $config[$phase][$phase.'RegistrantId'];
$admin = $config[$phase][$phase.'AdminId'];
$billing = $config[$phase][$phase.'BillingId'];
$tech = $config[$phase][$phase.'TechId'];
$authPw = $config[$phase][$phase.'AuthPw'];
$ns01 = $config[$phase][$phase.'Ns01'];
$ns02 = $config[$phase][$phase.'Ns02'];
$kType = $config[$phase][$phase.'KeyType'];
$ds01 = array();
$ds02 = array();
$ds03 = array();
$ds04 = array();
$kd01 = array();
$kd02 = array();
$kd03 = array();
$kd04 = array();
if ($kType == "D") {
	$ds01 = array ('keyTag'=>$config[$phase][$phase.'DsKeyTag01'],'alg'=>$config[$phase][$phase.'DsAlg01'],'dType'=>$config[$phase][$phase.'DsDigestType01'],'digest'=>$config[$phase][$phase.'DsDigest01']);
	if (array_key_exists($phase.'DsKeyTag02', $config[$phase])) {
		$ds02 = array ('keyTag'=>$config[$phase][$phase.'DsKeyTag02'],'alg'=>$config[$phase][$phase.'DsAlg02'],'dType'=>$config[$phase][$phase.'DsDigestType02'],'digest'=>$config[$phase][$phase.'DsDigest02']);
	}
	if (array_key_exists($phase.'DsKeyTag03', $config[$phase])) {
		$ds03 = array ('keyTag'=>$config[$phase][$phase.'DsKeyTag03'],'alg'=>$config[$phase][$phase.'DsAlg03'],'dType'=>$config[$phase][$phase.'DsDigestType03'],'digest'=>$config[$phase][$phase.'DsDigest03']);
	}
	if (array_key_exists($phase.'DsKeyTag04', $config[$phase])) {
		$ds04 = array ('keyTag'=>$config[$phase][$phase.'DsKeyTag04'],'alg'=>$config[$phase][$phase.'DsAlg04'],'dType'=>$config[$phase][$phase.'DsDigestType04'],'digest'=>$config[$phase][$phase.'DsDigest04']);
	}
}
if ($kType == "DK") {
	$ds01 = array ('keyTag'=>$config[$phase][$phase.'DsKeyTag01'],'alg'=>$config[$phase][$phase.'DsAlg01'],'dType'=>$config[$phase][$phase.'DsDigestType01'],'digest'=>$config[$phase][$phase.'DsDigest01']);
	if (array_key_exists($phase.'DsKeyTag02', $config[$phase])) {
		$ds02 = array ('keyTag'=>$config[$phase][$phase.'DsKeyTag02'],'alg'=>$config[$phase][$phase.'DsAlg02'],'dType'=>$config[$phase][$phase.'DsDigestType02'],'digest'=>$config[$phase][$phase.'DsDigest02']);
	}
	if (array_key_exists($phase.'DsKeyTag03', $config[$phase])) {
		$ds03 = array ('keyTag'=>$config[$phase][$phase.'DsKeyTag03'],'alg'=>$config[$phase][$phase.'DsAlg03'],'dType'=>$config[$phase][$phase.'DsDigestType03'],'digest'=>$config[$phase][$phase.'DsDigest03']);
	}
	if (array_key_exists($phase.'DsKeyTag04', $config[$phase])) {
		$ds04 = array ('keyTag'=>$config[$phase][$phase.'DsKeyTag04'],'alg'=>$config[$phase][$phase.'DsAlg04'],'dType'=>$config[$phase][$phase.'DsDigestType04'],'digest'=>$config[$phase][$phase.'DsDigest04']);
	}
	$kd01 = array ('flags'=>$config[$phase][$phase.'KdFlags01'],'protocol'=>$config[$phase][$phase.'KdProtocol01'],'alg'=>$config[$phase][$phase.'KdAlg01'],'pubKey'=>$config[$phase][$phase.'KdPubKey01']);
	if (array_key_exists($phase.'KdFlags02', $config[$phase])) {
		$kd02 = array ('flags'=>$config[$phase][$phase.'KdFlags02'],'protocol'=>$config[$phase][$phase.'KdProtocol02'],'alg'=>$config[$phase][$phase.'KdAlg02'],'pubKey'=>$config[$phase][$phase.'KdPubKey02']);
	}
	if (array_key_exists($phase.'KdFlags03', $config[$phase])) {
		$kd03 = array ('flags'=>$config[$phase][$phase.'KdFlags03'],'protocol'=>$config[$phase][$phase.'KdProtocol03'],'alg'=>$config[$phase][$phase.'KdAlg03'],'pubKey'=>$config[$phase][$phase.'KdPubKey03']);
	}
	if (array_key_exists($phase.'KdFlags04', $config[$phase])) {
		$kd04 = array ('flags'=>$config[$phase][$phase.'KdFlags04'],'protocol'=>$config[$phase][$phase.'KdProtocol04'],'alg'=>$config[$phase][$phase.'KdAlg04'],'pubKey'=>$config[$phase][$phase.'KdPubKey04']);
	}
}
if ($kType == "K") {
	$kd01 = array ('flags'=>$config[$phase][$phase.'KdFlags01'],'protocol'=>$config[$phase][$phase.'KdProtocol01'],'alg'=>$config[$phase][$phase.'KdAlg01'],'pubKey'=>$config[$phase][$phase.'KdPubKey01']);
	if (array_key_exists($phase.'KdFlags02', $config[$phase])) {
		$kd02 = array ('flags'=>$config[$phase][$phase.'KdFlags02'],'protocol'=>$config[$phase][$phase.'KdProtocol02'],'alg'=>$config[$phase][$phase.'KdAlg02'],'pubKey'=>$config[$phase][$phase.'KdPubKey02']);
	}
	if (array_key_exists($phase.'KdFlags03', $config[$phase])) {
		$kd03 = array ('flags'=>$config[$phase][$phase.'KdFlags03'],'protocol'=>$config[$phase][$phase.'KdProtocol03'],'alg'=>$config[$phase][$phase.'KdAlg03'],'pubKey'=>$config[$phase][$phase.'KdPubKey03']);
	}
	if (array_key_exists($phase.'KdFlags04', $config[$phase])) {
		$kd04 = array ('flags'=>$config[$phase][$phase.'KdFlags04'],'protocol'=>$config[$phase][$phase.'KdProtocol04'],'alg'=>$config[$phase][$phase.'KdAlg04'],'pubKey'=>$config[$phase][$phase.'KdPubKey04']);
	}
}

$domCre = new ireg_domain;
$domCre->name = $domain;
$domCre->insCont('owner', $registrant);
if ($admin != "") {
	$domCre->insCont('admin', $admin);
}
if ($billing != "") {
	$domCre->insCont('billing', $billing);
}
if ($tech != "") {
	$domCre->insCont('tech', $tech);
}
$domCre->authPw = $authPw;
$domCre->insNs(array('name'=>$ns01, 'addr_v4'=>array(), 'addr_v6'=>array()));
$domCre->insNs(array('name'=>$ns02, 'addr_v4'=>array(), 'addr_v6'=>array()));
if ($kType == "D") {
	$domCre->insSecDs($ds01);
	if (count($ds02) != 0) {
		$domCre->insSecDs($ds02);
	}
	if (count($ds03) != 0) {
		$domCre->insSecDs($ds03);
	}
	if (count($ds04) != 0) {
		$domCre->insSecDs($ds04);
	}
}
if ($kType == "DK") {
	$domCre->insSecDsKd($ds01,$kd01);
	if (count($ds02) != 0) {
		$domCre->insSecDsKd($ds02,$kd02);
	}
	if (count($ds03) != 0) {
		$domCre->insSecDsKd($ds03,$kd03);
	}
	if (count($ds04) != 0) {
		$domCre->insSecDsKd($ds04,$kd04);
	}
}
if ($kType == "K") {
	$domCre->insSecKd($kd01);
	if (count($kd02) != 0) {
		$domCre->insSecKd($kd02);
	}
	if (count($kd03) != 0) {
		$domCre->insSecKd($kd03);
	}
	if (count($kd04) != 0) {
		$domCre->insSecKd($kd04);
	}
}
if ($con->domCreate($domCre, $pno, $period)) {
	if (($con->getResCode() != "1000") && ($con->getResCode() != "1001")) {
		print "Domain Create (".$domain.") FAILED - ResultCode != 1000 (".$con->getResCode().")\n";
		$con->disconnect();
		exit (1);
	} else {
		print "Domain Create (".$domain.") OK - ResultCode = ".$con->getResCode()."\n";
		$exCode=0;
	}
} else {
	$reason=$con->getErrorReason();
	if (isset($reason) && $reason != '') {
	  $reason=" (".$reason.")";
	} else {
	  $reason="";
	}
	print "Domain create FAILED - ".$con->getResCode()." - ".$con->getResMsg().$reason."\n";
	$con->disconnect();
	exit (1);
}

// And disconnect
$con->disconnect();
if ($con->getResCode() != "1500") {
	print "Disconnect FAILED - ResultCOde != 1500 (".$con->getResCode().")\n";
	$exCode=1;
} else {
	print "Logout OK - ResultCode = ".$con->getResCode()."\n";
	$exCode=0;
}

exit($exCode);
?>
