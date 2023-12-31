# LytraAccessory
LytraAccessory is a Pocketmine MP Plugin that will add an accessory features on your server

# Register The Accessory
To register your accessory, you need to make a new yaml file on the plugin_data folder
For example the file name is "coolaccessory.yml"
Then inside the file, you need to insert a few things:
1. ID of the accessory [REQUIRED]
   ```yaml
   id: "coolaccessory"
   ```
2. Item ID (This one will determine the item that will used for the accessory), Supports for Vanilla Items and CustomItemLoader [REQUIRED]
   ```yaml
   item-id: "stick"
   ```
3. Custom Name (This one will determine the name of your accessory) [OPTIONAL]
   If you don't insert this, the name of the accessory will be the name of the item id above
   ```yaml
   custom-name: "Cool Accessory"
   ```
4. Lore (This one will determine the lore of your accessory) [OPTIONAL]
   ```yaml
   lore: ["Cool Accessory", "Very Cool"]
   ```
5. Damage Multiplier (This one will affect the damage stats of player)
   If you set it to 100%, then player will have 100% more damage
   If you set it to an integer like 1, then player will have 100% more damage
   1 = 100%, 2 = 200%, 3 = 300%, etc
   ```yaml
   damage-multiplier: 100%
   ```
   ```yaml
   damage-multiplier: 1
   ```
6. HP Multiplier (This one will affect the health stats of player)
   If you set it to 100%, then player will have 100% more hp
   If you set it to an integer like 1, then player will have 100% more hp
   1 = 100%, 2 = 200%, 3 = 300%, etc
   ```yaml
   hp-multiplier: 100%
   ```
   ```yaml
   hp-multiplier: 1
   ```
After that, you need to go to `config.yml` file, then add your file name to the loaded-accessory
```yaml
loaded-accessory: ["coolaccessory.yml"]
```

# Commands
| Command            | Description                                                          | Usage                                                |
| ------------------ | -------------------------------------------------------------------- | ---------------------------------------------------- |
| /la                | View your accessory inventory                                        | /la                                                  |
| /la get            | Get an accessory                                                     | /la get [string:id] [int:amount]                     |
| /la give           | Give an accessory to another player                                  | /la give [string:player] [string:id] [int:amount]    |
| /la info           | Get an information about a spesific accessory                        | /la info [string:id]                                 |
| /la remove         | Remove an equipped accessory from player                             | /la remove [string:player] [string:id]               |
| /la view           | View an accessory inventory of another player                        | /la view [string:player]                             |
| /la slots view     | Display information about total slots and empty slots of player      | /la slots view [string:player]                       |
| /la slots add      | Add a spesific amount of slots on accessory inventory                | /la slots add [string:player] [int:amount]           |
| /la slots remove   | Remove a spesific amount of slots on accessory inventory             | /la slots remove [string:player] [int:amount]        |
| /la slots set      | Set a spesific amount of slots on accessory inventory                | /la slots set [string:player] [int:amount]           |

# Permissions
You can use `lytraaccessory.command.admin` permission to give players all the lytra accessory commands permissions

| Command            | Permissions                                  | Default |
| ------------------ | -------------------------------------------- | ------- |
| /la                | lytraaccessory.command.use                   | True    |
| /la get            | lytraaccessory.command.admin.get             | OP      |
| /la give           | lytraaccessory.command.admin.give            | OP      |
| /la info           | lytraaccessory.command.admin.info            | OP      |
| /la remove         | lytraaccessory.command.admin.remove          | OP      |
| /la view           | lytraaccessory.command.admin.view            | OP      |
| /la slots view     | lytraaccessory.command.admin.slots.view      | OP      |
| /la slots add      | lytraaccessory.command.admin.slots.add       | OP      |
| /la slots remove   | lytraaccessory.command.admin.slots.remove    | OP      |
| /la slots set      | lytraaccessory.command.admin.slots.set       | OP      |

# For Developers
Importing the LytraAccessory main file
```php
use FresherGAMING\LytraAccessory\LytraAccessory;
```
If you want to get the registered accessory
```php
$id = //your accessory id
$amount = //the amount of the accessory do you want to get
LytraAccessory::getInstance()->getAccessory($id, $amount);
```

If you want to give an accessory to a player
```php
$player = //needs to be a player type not a string
$id = //your accessory id
$amount = //the amount of the accessory do you want to give to the player
LytraAccessory::getInstance()->giveAccessory($player, $id, $amount);
```

If you want to add an accessory to a player (simply make the player used an accessory)
```php
$player = //needs to be a player type not a string
$id = //your accessory id
LytraAccessory::getInstance()->addAccessory($player, $id);
```

If you want to remove an accessory from a player (simply make the player take of an accessory)
```php
$player = //needs to be a player type not a string
$id = //your accessory id
LytraAccessory::getInstance()->removeAccessory($player, $id);
```

If you want to get the number of players accessory slots
```php
$player = //needs to be a player type not a string
LytraAccessory::getInstance()->getSlots($player);
```

If you want to get the number of players empty accessory slots
```php
$player = //needs to be a player type not a string
LytraAccessory::getInstance()->getAvailableSlots($player);
```

If you want to set the number of players accessory slots
```php
$player = //needs to be a player type not a string
LytraAccessory::getInstance()->setSlots($player);
```
