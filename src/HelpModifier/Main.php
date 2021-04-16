<?php

namespace HelpModifier;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\Permission;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use HelpModifier\command\EnableHelpCommand;
use HelpModifier\command\DisableHelpCommand;

class Main extends PluginBase implements Listener
{

    public $pages = [];

    public function dataPath()
    {
        return $this->getDataFolder();
    }

    public function onEnable() : void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        @mkdir($this->dataPath());

        $this->saveDefaultConfig();
        foreach ($this->getConfig()->get("pages") as $page) {
            $this->pages[] = $page;
        }

        $this->getServer()->getPluginManager()->addPermission(new Permission("help.enable", "Allows user to use /enablehelp", Permission::DEFAULT_OP));
        $this->getServer()->getPluginManager()->addPermission(new Permission("help.disable", "Allows user to use /disablehelp", Permission::DEFAULT_OP));
        $this->getServer()->getCommandMap()->register("enablehelp", new EnableHelpCommand($this));
        $this->getServer()->getCommandMap()->register("disablehelp", new DisableHelpCommand($this));
    }

    public function onDisable() : void
    {
        $this->getConfig()->save();
    }

    private static function convert($input)
    {
        static $delims = " \-,.!?:;\\/&\(\)\[\]";
        static $tokens = [
            'zero' => ['val' => '0', 'power' => 1],
            'a' => ['val' => '1', 'power' => 1],
            'first' => ['val' => '1', 'suffix' => 'st', 'power' => 1],
            'one' => ['val' => '1', 'power' => 1],
            'second' => ['val' => '2', 'suffix' => 'nd', 'power' => 1],
            'two' => ['val' => '2', 'power' => 1],
            'third' => ['val' => '3', 'suffix' => 'rd', 'power' => 1],
            'three' => ['val' => '3', 'power' => 1],
            'fourth' => ['val' => '4', 'suffix' => 'th', 'power' => 1],
            'four' => ['val' => '4', 'power' => 1],
            'fifth' => ['val' => '5', 'suffix' => 'th', 'power' => 1],
            'five' => ['val' => '5', 'power' => 1],
            'sixth' => ['val' => '6', 'suffix' => 'th', 'power' => 1],
            'six' => ['val' => '6', 'power' => 1],
            'seventh' => ['val' => '7', 'suffix' => 'th', 'power' => 1],
            'seven' => ['val' => '7', 'power' => 1],
            'eighth' => ['val' => '8', 'suffix' => 'th', 'power' => 1],
            'eight' => ['val' => '8', 'power' => 1],
            'ninth' => ['val' => '9', 'suffix' => 'th', 'power' => 1],
            'nine' => ['val' => '9', 'power' => 1],
            'tenth' => ['val' => '10', 'suffix' => 'th', 'power' => 1],
            'ten' => ['val' => '10', 'power' => 10],
            'eleventh' => ['val' => '11', 'suffix' => 'th', 'power' => 10],
            'eleven' => ['val' => '11', 'power' => 10],
            'twelveth' => ['val' => '12', 'suffix' => 'th', 'power' => 10],
            'twelfth' => ['val' => '12', 'suffix' => 'th', 'power' => 10],
            'twelve' => ['val' => '12', 'power' => 10],
            'thirteenth' => ['val' => '13', 'suffix' => 'th', 'power' => 10],
            'thirteen' => ['val' => '13', 'power' => 10],
            'fourteenth' => ['val' => '14', 'suffix' => 'th', 'power' => 10],
            'fourteen' => ['val' => '14', 'power' => 10],
            'fifteenth' => ['val' => '15', 'suffix' => 'th', 'power' => 10],
            'fifteen' => ['val' => '15', 'power' => 10],
            'sixteenth' => ['val' => '16', 'suffix' => 'th', 'power' => 10],
            'sixteen' => ['val' => '16', 'power' => 10],
            'seventeenth' => ['val' => '17', 'suffix' => 'th', 'power' => 10],
            'seventeen' => ['val' => '17', 'power' => 10],
            'eighteenth' => ['val' => '18', 'suffix' => 'th', 'power' => 10],
            'eighteen' => ['val' => '18', 'power' => 10],
            'nineteenth' => ['val' => '19', 'suffix' => 'th', 'power' => 10],
            'nineteen' => ['val' => '19', 'power' => 10],
            'twentieth' => ['val' => '20', 'suffix' => 'th', 'power' => 10],
            'twenty' => ['val' => '20', 'power' => 10],
            'thirty' => ['val' => '30', 'power' => 10],
            'forty' => ['val' => '40', 'power' => 10],
            'fifty' => ['val' => '50', 'power' => 10],
            'sixty' => ['val' => '60', 'power' => 10],
            'seventy' => ['val' => '70', 'power' => 10],
            'eighty' => ['val' => '80', 'power' => 10],
            'ninety' => ['val' => '90', 'power' => 10],
            'hundred' => ['val' => '100', 'power' => 100],
            'thousand' => ['val' => '1000', 'power' => 1000],
            'million' => ['val' => '1000000', 'power' => 1000000],
            'billion' => ['val' => '1000000000', 'power' => 1000000000],
            'and' => ['val' => '', 'power' => null],
        ];
        $powers = array_column($tokens, 'power', 'val');
        $mutate = function ($parts) use (&$mutate, $powers){
            $stack = new \SplStack;
            $sum   = 0;
            $last  = null;
            foreach ($parts as $idx => $arr) {
                $part = $arr['val'];
                if (!$stack->isEmpty()) {
                    $check = $last ?? $part;
                    if ((float)$stack->top() < 20 && (float)$part < 20 ?? (float)$part < $stack->top() ) {
                        return $stack->top() . (isset($parts[$idx - $stack->count()]['suffix']) ? $parts[$idx - $stack->count()]['suffix'] : '') . " " . $mutate(array_slice($parts, $idx));
                    }
                    if (isset($powers[$check]) && $powers[$check] <= $arr['power'] && $arr['power'] <= 10) {
                        return $stack->top() . (isset($parts[$idx - $stack->count()]['suffix']) ? $parts[$idx - $stack->count()]['suffix'] : '') . " " . $mutate(array_slice($parts, $idx));
                    }
                    if ($stack->top() > $part) {
                        if ($last >= 1000) {
                            $sum += $stack->pop();
                            $stack->push($part);
                        } else {
                            $stack->push($stack->pop() + (float) $part);
                        }
                    } else {
                        $current = $stack->pop();
                        if (is_numeric($current)) {
                            $stack->push($current * (float) $part);
                        } else {
                            $stack->push($part);
                        }
                    }
                } else {
                    $stack->push($part);
                }

                $last = $part;
            }
            return $sum + $stack->pop();
        };
        $prepared = preg_split('/([' . $delims . '])/', $input, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($prepared as $idx => $word) {
            if (is_array($word)) {
                continue;
            }
            $maybeNumPart = trim(strtolower($word));
            if (isset($tokens[$maybeNumPart])) {
                $item = $tokens[$maybeNumPart];
                if (isset($prepared[$idx + 1])) {
                    $maybeDelim = $prepared[$idx + 1];
                    if ($maybeDelim === " ") {
                        $item['delim'] = $maybeDelim;
                        unset($prepared[$idx + 1]);
                    } elseif ($item['power'] == null && !isset($tokens[$maybeDelim])) {
                        continue;
                    }
                }
                $prepared[$idx] = $item;
            }
        }
        $result = [];
        $accumulator = [];
        $getNumeral = function () use ($mutate, &$accumulator, &$result) {
            $last = end($accumulator);
            $result[] = $mutate($accumulator) . (isset($last['suffix']) ? $last['suffix'] : '') . (isset($last['delim']) ? $last['delim'] : '');
            $accumulator = [];
        };
        foreach ($prepared as $part) {
            if (is_array($part)) {
                $accumulator[] = $part;
            } else {
                if (!empty($accumulator)) {
                    $getNumeral();
                }
                $result[] = $part;
            }
        }
        if (!empty($accumulator)) {
            $getNumeral();
        }
        return implode('', array_filter($result));
    }

    private function getIndex(string $stringNumber)
    {
        if (is_numeric($stringNumber)) {
            $number = (int) $stringNumber;
        } else {
            $number = (int) self::convert($stringNumber);
        }
        if (!is_numeric($number)) { // prevent converter returning string
            return 0;
        }
        if ($number >= 1) {
            return $number - 1;
        }
        return 0;
    }

    public function sendHelp(PlayerCommandPreprocessEvent $event)
    {
        $command = explode(" ", strtolower($event->getMessage()));
        $player = $event->getPlayer();
        $helpCommands = ["/help", "/?", "/pocketmine:help", "/pocketmine:?"];
        if (in_array($command[0], $helpCommands)) {
            if ($this->getConfig()->get("enable-help") == false) {
                return;
            }

            if (isset($command[1])) {
                $index = $this->getIndex($command[1]);
                $messages = $this->pages[$index];
                foreach ($messages as $message) {
                    $player->sendMessage($message);
                }
            } else {
                $messages = $this->pages[0];
                foreach ($messages as $message) {
                    $player->sendMessage($message);
                }
            }
        }
    }

}
