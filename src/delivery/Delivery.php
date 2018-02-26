<?php

namespace delivery;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Delivery extends PluginBase {
    private $data = [];
    public function onEnable() {
        if(!file_exists($this->getDataFolder())) {
            @mkdir($this->getDataFolder());
        }
        if(!file_exists($this->getDataFolder() . DIRECTORY_SEPARATOR . 'data.json')){
            file_put_contents($this->getDataFolder() . DIRECTORY_SEPARATOR . 'data.json', json_encode($this->data));
        }
        $this->load();
    }
    private function load() {
        $this->data = json_decode(file_get_contents($this->getDataFolder() . DIRECTORY_SEPARATOR . 'data.json'), true);
        $newdata = [];
        foreach($this->data as $player => $boxes) {
        	foreach($boxes as $box => $data) {
        		if($box == 'sendbox') {
        			foreach($data as $data2 => $val) {
        				$newdata[$player][$box][$data2] = unserialize($val);
        			}
        		} else {
        			foreach($data as $delivery => $items) {
        				foreach($items as $data2 => $val) {
        					if($data2 == 'from' or $data2 == 'name') {
        						$newdata[$player][$box][$delivery][$data2] = $val;
        					} else {
        						$newdata[$player][$box][$delivery][$data2] = unserialize($val);
        					}
        				}
        			}
        		}
        	}
        }
        $this->data = $newdata;
    }
    private function save() {
    	$newdata = [];
    	foreach($this->data as $player => $boxes) {
    		foreach($boxes as $box => $data) {
    			if($box == 'sendbox') {
    				foreach($data as $data2 => $val) {
    					$newdata[$player][$box][$data2] = serialize($val);
    				}
    			} else {
    				foreach($data as $delivery => $items) {
    					foreach($items as $data2 => $val) {
    						if($data2 == 'from' or $data2 == 'name') {
    							$newdata[$player][$box][$delivery][$data2] = $val;
    						} else {
    							$newdata[$player][$box][$delivery][$data2] = serialize($val);
    						}
    					}
    				}
    			}
    		}
    	}
        file_put_contents($this->getDataFolder() . DIRECTORY_SEPARATOR . 'data.json', json_encode($newdata));
    }
    static function dropTo(Player $player, array $items) {
    	foreach($items as $item) {
    		$item instanceof Item;
    		$player->dropItem($item);
    	}
    	return;
    }
    public function onCommand(CommandSender $sender, Command $command,string $label,array $args) : bool {
        $this->load();
        if($sender instanceof Player) {} else {
            $sender->sendMessage(TextFormat::RED . '이 명령어는 인게임에서만 실행 가능합니다');
            return true;
        }
        if(!isset($args[0])) {
            $sender->sendMessage('/택배 담기 <갯수>');
            $sender->sendMessage('-택배에 손에든 물건을 추가합니다');
            $sender->sendMessage('/택배 초기화');
            $sender->sendMessage('-택배에 담은것들을 초기화 합니다');
            $sender->sendMessage('/택배 담은것');
            $sender->sendMessage('-택배에 담아진 물건 목록을 확인합니다');
            $sender->sendMessage('/택배 send <플레이어> <택배제목(선택사항)>');
            $sender->sendMessage('-플레이어에게 택배를 전속합니다');
            $sender->sendMessage('/택배 목록');
            $sender->sendMessage('-받은 택배 목록을 확인합니다');
            $sender->sendMessage('/택배 받기 <택배번호>');
            $sender->sendMessage('-택배를 수령합니다');
            return true;
        }
        switch ($args[0]) {
            case '담기':
                if(!isset($args[1])) {
                    $sender->sendMessage('/택배 담기' . TextFormat::RED . '<갯수>');
                    break;
                }
                if(!is_numeric($args[1])) {
                	$sender->sendMessage('갯수가 숫자가 아닙니다');
                	break;
                }
                if($sender->getInventory()->getItemInHand()->getCount() < (int)$args[1]) {
                    $sender->sendMessage('아이템의 갯수가 부족합니다');
                    break;
                }
                $sender->getInventory()->getItemInHand()->setCount((int)$args[1]);
                $item = $sender->getInventory()->getItemInHand();
                if(!isset($this->data[strtolower($sender->getName())]['sendbox'])) $this->data[strtolower($sender->getName())]['sendbox'] = [];
                foreach ($this->data[strtolower($sender->getName())]['sendbox'] as $key => $i){
                    $i instanceof Item;
                    $i->setCount(1);
                    $item->setCount(1);
                    if(json_encode($i) == json_encode($item)) {
                        $this->data[strtolower($sender->getName())]['sendbox'][$key] instanceof Item;
                        if($item->getMaxStackSize() < $this->data[strtolower($sender->getName())]['sendbox'][$key]->getCount() + (int)$args[1]) {
                            continue;
                        }
                        $this->data[strtolower($sender->getName())]['sendbox'][$key]->setCount($this->data[strtolower($sender->getName())]['sendbox'][$key]->getCount() + (int)$args[1]);
                        $sender->sendMessage($item->getName() . '을 ' . (int)$args[1] . '개 보낼 상자에 추가하였습니다.');
                        break;
                    }
                }
                if(count($this->data[strtolower($sender->getName())]['sendbox']) >= 10) {
                	$sender->sendMessage('10가지 이상의 아이템을 택배에 담을 수 없습니다(상자로서 악용 방지)');
                	break;
                }
                $item->setCount((int)$args[1]);
                $this->data[strtolower($sender->getName())]['sendbox'][] = $item;
                $sender->sendMessage($item->getName() . '을 ' . (int)$args[1] . '개 보낼 상자에 추가하였습니다.');
                $this->save();
                break;
            case '초기화':
            	if(!isset($this->data[strtolower($sender->getName())]['sendbox'])) {
            		$sender->sendMessage('택배에 담은 물건이 없습니다');
            		break;
            	}
            	self::dropTo($sender, $this->data[strtolower($sender->getName())]['sendbox']);
            	unset($this->data[strtolower($sender->getName())]['sendbox']);
            	$sender->sendMessage('택배에 담은 물건을 초기화했습니다');
            	$this->save();
            	break;
            case '담은것':
            	if(!isset($this->data[strtolower($sender->getName())]['sendbox'])) {
            		$sender->sendMessage('보낼 택배에 담은 물건이 없습니다.');
            		break;
            	}
            	foreach ($this->data[strtolower($sender->getName())]['sendbox'] as $item) {
            		$sender->sendMessage('-' . $item->getName() . ' ' . $item->getCount() . '개');
            	}
            	break;
            case 'send'://TODO 한글
            	if(!isset($args[1])) {
            		$sender->sendMessage('/택배 보내기' . TextFormat::RED . '<플레이어> ' . TextFormat::RESET . '<택배제목(선택사항)>');
            		break;
            	}
            	if(!isset($this->data[strtolower($sender->getName())]['sendbox'])) {
            		$sender->sendMessage('보낼 택배가 없습니다');
            	}
            	foreach (scandir(Server::getInstance()->getDataPath() . DIRECTORY_SEPARATOR . 'players' . DIRECTORY_SEPARATOR) as $plname) {
            		if(strtolower($plname) == strtolower($args[1]) . '.dat') {
            			$this->data[strtolower($sender->getName())]['sendbox']['from'] = strtolower($sender->getName());
            			$this->data[strtolower($sender->getName())]['sendbox']['name'] = isset($args[2]) ? $args[2] : '없음';
            			if(!isset($this->data[strtolower($args[1])]['inbox'])) {
            				$this->data[strtolower($args[1])]['inbox'] = [];
            			}
            			$this->data[strtolower($args[1])]['inbox'][] = $this->data[strtolower($sender->getName())]['sendbox'];
            			unset($this->data[strtolower($sender->getName())]['sendbox']);
            			$sender->sendMessage(substr($plname, 0, -4) . '에게 택배를 보냈습니다');
            			$this->save();
            			break 2;
            		}
            	}
            	$sender->sendMessage('이 플레이어는 한번도 접속하지 않았습니다');
            	break;
            case '목록':
            	if(!isset($this->data[strtolower($sender->getName())]['inbox'])) {
            		$sender->sendMessage('받은 택배가 없습니다');
            		break;
            	}
            	foreach($this->data[strtolower($sender->getName())]['inbox'] as $key => $boxes) {
            		$sender->sendMessage($key . '. 제목: ' . $boxes['name'] . ' from ' . $boxes['from']);
            	}
            	break;
            case '받기':
            	if(!isset($args[1])) {
            		$sender->sendMessage('/택배 받기 ' . TextFormat::RED . '<택배번호>');
            		break;
            	}
            	if(!is_numeric($args[1])) {
            		$sender->sendMessage('택배번호가 숫자가 아닙니다');
            		break;
            	}
            	if(!isset($this->data[strtolower($sender->getName())]['inbox'][(int)$args[1]])) {
            		$sender->sendMessage('없는 택배 번호입니다');
            		break;
            	}
            	unset($this->data[strtolower($sender->getName())]['inbox'][(int)$args[1]]['from']);
            	unset($this->data[strtolower($sender->getName())]['inbox'][(int)$args[1]]['name']);
            	self::dropTo($sender, $this->data[strtolower($sender->getName())]['inbox'][(int)$args[1]]);
            	unset($this->data[strtolower($sender->getName())]['inbox'][(int)$args[1]]);
            	$newArr = [];
            	foreach($this->data[strtolower($sender->getName())]['inbox'] as $inbox) {
            		$newArr[] = $inbox;
            	}
            	$this->data[strtolower($sender->getName())]['inbox'] = $newArr;
            	$sender->sendMessage('택배를 수령했습니다');
            	$this->save();
        }
        return true;
    }
}