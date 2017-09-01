<?php
/*
 Author: Andrews54757
 License: MIT (https://github.com/ThreeLetters/SuperSQL/blob/master/LICENSE)
 Source: https://github.com/ThreeLetters/SQL-Library
 Build: v1.0.8
 Built on: 01/09/2017
*/

namespace SuperSQL;

// lib/connector.php
class Response implements \ArrayAccess,\Iterator{public$result;public$affected;public$ind=0;public$error;public$errorData;public$outTypes;public$complete=false;function __construct($a,$b,&$c,$d){$this->error=!$b;if(!$b){$this->errorData=$a->errorInfo();}else{$this->outTypes=$c;$this->init($a,$d);$this->affected=$a->rowCount();}}private function init(&$a,&$d){if($d===0){$c=$this->outTypes;$e=$a->fetchAll(\PDO::FETCH_ASSOC);if($c){foreach($e as$f=>&$g){$this->map($g,$c);}}$this->result=$e;$this->complete=true;}else if($d===1){$this->stmt=$a;$this->result=array();}}function close(){$this->complete=true;if($this->stmt){$this->stmt->closeCursor();$this->stmt=null;}}private function fetchNextRow(){$g=$this->stmt->fetch(\PDO::FETCH_ASSOC);if($g){if($this->outTypes){$this->map($g,$this->outTypes);}array_push($this->result,$g);return$g;}else{$this->complete=true;$this->stmt->closeCursor();$this->stmt=null;return false;}}private function fetchAll(){while($this->fetchNextRow()){}}function map(&$g,&$c){foreach($c as$h=>$i){if(isset($g[$h])){switch($i){case 'int':$g[$h]=(int)$g[$h];break;case 'string':$g[$h]=(string)$g[$h];break;case 'bool':$g[$h]=$g[$h]?true:false;break;case 'json':$g[$h]=json_decode($g[$h]);break;case 'obj':$g[$h]=unserialize($g[$h]);break;}}}}function error(){return$this->error?$this->errorData:false;}function getData($j=false){if(!$this->complete&&!$j)$this->fetchAll();return$this->result;}function getAffected(){return$this->affected;}function countRows(){return count($this->result);}function offsetSet($k,$l){}function offsetExists($k){return$this->offsetGet($k)===null?false:true;}function offsetUnset($k){}function offsetGet($k){if(is_int($k)){if(isset($this->result[$k])){return$this->result[$k];}else if(!$this->complete){while($this->fetchNextRow()){if(isset($this->result[$k]))return$this->result[$k];}}}return null;}function next(){if(isset($this->result[$this->ind])){return$this->result[$this->ind++];}else if(!$this->complete){$g=$this->fetchNextRow();$this->ind++;return$g;}else{return false;}}function rewind(){$this->ind=0;}function current(){return$this->result[$this->ind];}function key(){return$this->ind;}function valid(){return$this->offsetExists($this->ind);}}class Connector{public$db;public$log=array();public$dev=false;function __construct($m,$n,$o){$this->db=new \PDO($m,$n,$o);$this->log=array();}function query($p,$q=null,$c=null,$d=0){$r=$this->db->prepare($p);if($q)$s=$r->execute($q);else$s=$r->execute();if($this->dev)array_push($this->log,array($p,$q));if($d!==3){return new Response($r,$s,$c,$d);}else{return$r;}}function _query(&$t,$u,&$v,&$c=null,$d=0){$r=$this->db->prepare($t);if($this->dev)array_push($this->log,array($t,$u,$v));foreach($u as$w=>&$x){$r->bindParam($w+1,$x[0],$x[1]);}$s=$r->execute();if(!isset($v[0])){return new Response($r,$s,$c,$d);}else{$y=array();array_push($y,new Response($r,$s,$c,0));foreach($v as$w=>$l){foreach($l as$z=>&$aa){$u[$z][0]=$aa;}$s=$r->execute();array_push($y,new Response($r,$s,$c,0));}return$y;}}function close(){$this->db=null;$this->queries=null;}}
// lib/parser.php
class Parser{static function getArg(&$a){preg_match('/^(?:\[(?<a>.{2})\])(?<out>.*)/',$a,$b);if(isset($b['a'])){$a=$b['out'];return$b['a'];}else{return false;}}static function isRaw(&$c){if($c[0]==='#'){$c=substr($c,1);return true;}return false;}static function append(&$d,$e,$f,$g){if(is_array($e)&&$g[$f][2]<5){$h=count($e);for($i=1;$i<$h;$i++){if(!isset($d[$i-1]))$d[$i-1]=array();$d[$i-1][$f]=$e[$i];}}}static function escape($e){if(is_int($e)){return(int)$e;}else{return '\''.$e.'\'';}}static function escape2($e,$j){switch($j[2]){case 0:return$e?'1':'0';break;case 1:return(int)$e;break;case 2:return(string)$e;break;case 3:return$e;break;case 4:return null;break;case 5:return json_encode($e);break;case 6:return serialize($e);break;}}static function stripArgs(&$c){preg_match('/(?:\[.{2}\]){0,2}([^\[]*)/',$c,$k);return$k[1];}static function append2(&$l,$m,$j,$g){$h=count($j);for($c=1;$c<$h;$c++){$e=$j[$c];if(!isset($l[$c-1]))$l[$c-1]=array();self::recurse($l[$c-1],$e,$m,'',$g);}}private static function recurse(&$n,$e,$m,$o,$g){foreach($e as$i=>&$p){if($i[0]==='#')continue;self::stripArgs($i);$q=$i.'#'.$o;if(isset($m[$q]))$r=$m[$q];else$r=$m[$i];$s=is_array($p)&&(!isset($g[$r][2])||$g[$r][2]<5);if($s){if(isset($p[0])){foreach($p as$t=>&$u){$v=$r+$t;if(isset($n[$v]))trigger_error('Key collision: '.$i,E_USER_WARNING);$n[$v]=self::escape2($u,$g[$v]);}}else{self::recurse($n,$p,$m,$o.'/'.$i,$g);}}else{if(isset($n[$r]))trigger_error('Key collision: '.$i,E_USER_WARNING);$n[$r]=self::escape2($p,$g[$r]);}}}static function quote($a){preg_match('/([^.]*)\.?(.*)?/',$a,$k);if($k[2]!==''){return '`'.$k[1].'.'.$k[2].'`';}else{return '`'.$k[1].'`';}}static function quoteArray(&$w){foreach($w as&$p){$p=self::quote($p);}}static function table($x){if(is_array($x)){$y='';foreach($x as$t=>&$e){$z=self::getType($e);if($t!==0)$y.=', ';$y.='`'.$e.'`';if($z)$y.=' AS `'.$z.'`';}return$y;}else{return '`'.$x.'`';}}static function value($aa,$ba){$ca=$aa?$aa:gettype($ba);$aa=\PDO::PARAM_STR;$da=2;if($ca==='integer'||$ca==='int'||$ca==='double'||$ca==='doub'){$aa=\PDO::PARAM_INT;$da=1;$ba=(int)$ba;}else if($ca==='string'||$ca==='str'){$ba=(string)$ba;$da=2;}else if($ca==='boolean'||$ca==='bool'){$aa=\PDO::PARAM_BOOL;$ba=$ba?'1':'0';$da=0;}else if($ca==='null'||$ca==='NULL'){$da=4;$aa=\PDO::PARAM_NULL;$ba=null;}else if($ca==='resource'||$ca==='lob'){$aa=\PDO::PARAM_LOB;$da=3;}else if($ca==='json'){$da=5;$ba=json_encode($ba);}else if($ca==='obj'||$ca==='object'){$da=6;$ba=serialize($ba);}else{$ba=(string)$ba;trigger_error('Invalid type '.$ca.' Assumed STRING',E_USER_WARNING);}return array($ba,$aa,$da);}static function getType(&$a){preg_match('/(?<out>[^\[]*)(?:\[(?<a>[^\]]*)\])?/',$a,$b);$a=$b['out'];return isset($b['a'])?$b['a']:false;}static function rmComments($a){preg_match('/([^#]*)/',$a,$k);return$k[1];}static function conditions($j,&$g=false,&$ea=false,&$f=0,$fa=' AND ',$ga=' = ',$ha=''){$ia=0;$y='';foreach($j as$c=>&$e){preg_match('^(?<r>\#)?(?:\[(?<a>.{2})\])(?:\[(?<b>.{2})\])?(?<out>.*)',$c,$k);$ja=isset($k['r']);if(isset($k['a'])){$ka=$k['a'];$c=$k['out'];$la=isset($k['b'])?$k['b']:false;}else{$ka=false;}$ma=!isset($e[0]);$na=$fa;$oa=$ga;$aa=$ja?false:self::getType($c);$w=is_array($e)&&$aa!=='json'&&$aa!=='obj';if($ka&&($ka==='||'||$ka==='&&')){$na=($ka==='||')?' OR ':' AND ';$ka=$la;if($w&&$ka&&($ka==='||'||$ka==='&&')){$fa=$na;$na=($ka==='||')?' OR ':' AND ';$ka=self::getArg($c);}}$pa=false;if($ka&&$ka!=='=='){switch($ka){case '!=':$oa=' != ';break;case '>>':$oa=' > ';break;case '<<':$oa=' < ';break;case '>=':$oa=' >= ';break;case '<=':$oa=' <= ';break;case '~~':$oa=' LIKE ';break;case '!~':$oa=' NOT LIKE ';break;default:if($ka!=='><'&&$ka!=='<>')throw new \Exception('Invalid operator '.$ka.' Available: ==,!=,>>,<<,>=,<=,~~,!~,<>,><');else$pa=true;break;}}else{if(!$ma||$ka==='==')$oa=' = ';}if(!$w)$fa=$na;if($ia!==0)$y.=$fa;$qa=self::rmComments($c);if(!$ja)$qa=self::quote($qa);if($w){$y.='(';if($ma){$y.=self::conditions($e,$g,$ea,$f,$na,$oa,$ha.'/'.$c);}else{if($ea!==false&&!$ja){$ea[$c]=$f;$ea[$c.'#'.$ha]=$f++;}if($pa){$f+=2;$y.=$qa.($ka==='<>'?'NOT':'').' BETWEEN ';if($ja){$y.=$e[0].' AND '.$e[1];}else if($g!==false){$y.='? AND ?';array_push($g,self::value($aa,$e[0]));array_push($g,self::value($aa,$e[1]));}else{$y.=self::escape($e[0]).' AND '.self::escape($e[1]);}}else{foreach($e as$i=>&$p){if($i!==0)$y.=$na;++$f;$y.=$qa.$oa;if($ja){$y.=$p;}else if($g!==false){$y.='?';array_push($g,self::value($aa,$p));}else{$y.=self::escape($p);}}}}$y.=')';}else{$y.=$qa.$oa;if($ja){$y.=$e;}else{if($g!==false){$y.='?';array_push($g,self::value($aa,$e));}else{$y.=self::escape($e);}if($ea!==false){$ea[$c]=$f;$ea[$c.'#'.$ha]=$f++;}}}++$ia;}return$y;}static function JOIN($fa,&$y,&$g,&$t){foreach($fa as$c=>&$e){$ja=self::isRaw($c);$ka=self::getArg($c);switch($ka){case '<<':$y.=' RIGHT JOIN ';break;case '>>':$y.=' LEFT JOIN ';break;case '<>':$y.=' FULL JOIN ';break;case '>~':$y.=' LEFT OUTER JOIN ';break;default:$y.=' JOIN ';break;}$y.='`'.$c.'` ON ';if($ja){$y.=$e;}else{$y.=self::conditions($e,$g,$ra,$t);}}}static function columns($sa,&$y,&$ta){$ua='';$ra=$sa[0][0];if($ra==='D'||$ra==='I'){if($sa[0]==='DISTINCT'){$y.='DISTINCT ';array_splice($sa,0,1);}else if(substr($sa[0],0,11)==='INSERT INTO'){$y=$sa[0].' '.$y;array_splice($sa,0,1);}else if(substr($sa[0],0,4)==='INTO'){$ua=' '.$sa[0].' ';array_splice($sa,0,1);}}if(isset($sa[0])){if($sa[0]==='*'){array_splice($sa,0,1);$y.='*';foreach($sa as$t=>&$e){preg_match('/(?<column>[a-zA-Z0-9_\.]*)(?:\[(?<type>[^\]]*)\])?/',$e,$va);$ta[$va['column']]=$va['type'];}}else{foreach($sa as$t=>&$e){preg_match('/(?<column>[a-zA-Z0-9_\.]*)(?:\[(?<alias>[^\]]*)\])?(?:\[(?<type>[^\]]*)\])?/',$e,$va);$e=$va['column'];$wa=false;if(isset($va['alias'])){$wa=$va['alias'];if(isset($va['type'])){$aa=$va['type'];}else{if($wa==='json'||$wa==='obj'||$wa==='int'||$wa==='string'||$wa==='bool'){$aa=$wa;$wa=false;}else$aa=false;}if($aa){if(!$ta)$ta=array();$ta[$wa?$wa:$e]=$aa;}}if($t!==0){$y.=', ';}$y.=self::quote($e);if($wa)$y.=' AS `'.$wa.'`';}}}else$y.='*';$y.=$ua;}static function SELECT($x,$sa,$xa,$fa,$ya){$y='SELECT ';$g=$l=array();$ta=null;$t=0;if(!isset($sa[0])){$y.='*';}else{self::columns($sa,$y,$ta);}$y.=' FROM '.self::table($x);if($fa){self::JOIN($fa,$y,$g,$t);}if(!empty($xa)){$y.=' WHERE ';if(isset($xa[0])){$f=array();$y.=self::conditions($xa[0],$g,$f,$t);self::append2($l,$f,$xa,$g);}else{$y.=self::conditions($xa,$g);}}if($ya){if(is_int($ya)){$y.=' LIMIT '.$ya;}else if(is_string($ya)){$y.=' '.$ya;}else if(is_array($ya)){if(isset($ya[0])){$y.=' LIMIT '.(int)$ya[0].' OFFSET '.(int)$ya[1];}else{if(isset($ya['GROUP'])){$y.=' GROUP BY ';if(is_string($ya['GROUP'])){$y.=self::quote($ya['GROUP']);}else{self::quoteArray($ya['GROUP']);$y.=implode(', ',$ya['GROUP']);}if(isset($ya['HAVING'])){$y.=' HAVING '.(is_string($ya['HAVING'])?$ya['HAVING']:self::conditions($ya['HAVING'],$g,$ra,$t));}}if(isset($ya['ORDER'])){$y.=' ORDER BY '.self::quote($ya['ORDER']);}if(isset($ya['LIMIT'])){$y.=' LIMIT '.(int)$ya['LIMIT'];}if(isset($ya['OFFSET'])){$y.=' OFFSET '.(int)$ya['OFFSET'];}}}}return array($y,$g,$l,$ta);}static function INSERT($x,$za,$ab){$y='INSERT INTO '.self::table($x).' (';$g=$l=$f=array();$bb='';$cb=0;$db=isset($za[0]);$j=$db?$za[0]:$za;foreach($j as$c=>$e){$ja=self::isRaw($c);if($cb){$y.=', ';$bb.=', ';}else$cb=1;if(!$ja){preg_match('/(?<out>[^\[]*)(?:\[(?<type>[^]]*)\])?/',$c,$k);$c=$k['out'];}$y.='`'.$c.'`';if($ja){$bb.=$e;}else{$aa=isset($k['type'])?$k['type']:false;$bb.='?';$eb=!$db&&(!$aa||($aa!=='json'&&$aa!=='obj'))&&is_array($e);array_push($g,self::value($aa,$eb?$e[0]:$e));if($db){$f[$c]=array($e,$aa);}else if($eb){self::append($l,$e,$t++,$g);}}}$y.=') VALUES ('.$bb.')';if($db){unset($za[0]);foreach($za as$fb){$y.=', ('.$bb.')';foreach($f as$c=>$e){array_push($g,self::value($e[1],isset($fb[$c])?$fb[$c]:$e[0]));}}}if($ab)$y.=' '.$ab;return array($y,$g,$l);}static function UPDATE($x,$za,$xa){$y='UPDATE '.self::table($x).' SET ';$g=$l=$m=array();$t=$cb=0;$db=isset($za[0]);$j=$db?$za[0]:$za;foreach($j as$c=>&$e){$ja=self::isRaw($c);if($cb){$y.=', ';}else$cb=1;if($ja){$y.='`'.$c.'` = '.$e;}else{preg_match('/(?:\[(?<arg>.{2})\])?(?<out>[^\[]*)(?:\[(?<type>[^\]]*)\])?/',$c,$k);$c=$k['out'];$y.='`'.$c.'` = ';if(isset($k['arg'])){switch($k['arg']){case '+=':$y.='`'.$c.'` + ?';break;case '-=':$y.='`'.$c.'` - ?';break;case '/=':$y.='`'.$c.'` / ?';break;case '*=':$y.='`'.$c.'` * ?';break;default:$y.='?';break;}}$aa=isset($k['type'])?$k['type']:false;$eb=(!$aa||($aa!=='json'&&$aa!=='obj'))&&is_array($e);array_push($g,self::value($aa,$eb?$e[0]:$e));if($db){$m[$c]=$t++;}else if($eb){self::append($l,$e,$t++,$g);}}}if($db)self::append2($l,$m,$za,$g);if(!empty($xa)){$y.=' WHERE ';$f=array();if(isset($xa[0])){$y.=self::conditions($xa[0],$g,$f,$t);self::append2($l,$f,$xa,$g);}else{$y.=self::conditions($xa,$g,$ra,$t);}}return array($y,$g,$l);}static function DELETE($x,$xa){$y='DELETE FROM '.self::table($x);$g=$l=array();if(!empty($xa)){$y.=' WHERE ';$f=array();if(isset($xa[0])){$y.=self::conditions($xa[0],$g,$f);self::append2($l,$f,$xa,$g);}else{$y.=self::conditions($xa,$g);}}return array($y,$g,$l);}}
// index.php
class SuperSQL{public$con;public$lockMode=false;function __construct($a,$b,$c){$this->con=new Connector($a,$b,$c);}function SELECT($d,$e=array(),$f=array(),$g=null,$h=false){if((is_int($g)||is_string($g)||isset($g[0]))&&!$h){$h=$g;$g=null;}$i=Parser::SELECT($d,$e,$f,$g,$h);return$this->con->_query($i[0],$i[1],$i[2],$i[3],$this->lockMode?0:1);}function INSERT($d,$j,$k=null){$i=Parser::INSERT($d,$j,$k);return$this->con->_query($i[0],$i[1],$i[2]);}function UPDATE($d,$j,$f=array()){$i=Parser::UPDATE($d,$j,$f);return$this->con->_query($i[0],$i[1],$i[2]);}function DELETE($d,$f=array()){$i=Parser::DELETE($d,$f);return$this->con->_query($i[0],$i[1],$i[2]);}function query($l,$m=null,$n=null,$o=0){return$this->con->query($l,$m,$n,$o);}function close(){$this->con->close();}function dev(){$this->con->dev=true;}function getLog(){return$this->con->log;}function transact($p){$this->con->db->beginTransaction();$q=$p($this);if($q===false)$this->con->db->rollBack();else$this->con->db->commit();return$q;}function modeLock($r){$this->lockMode=$r;}}
?>