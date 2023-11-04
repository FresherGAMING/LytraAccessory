# LytraAccessory
LytraAccessory is a Pocketmine MP Plugin that will add accessory features on your server

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

   
