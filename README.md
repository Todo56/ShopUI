## Information
ShopUI is a simple shop system with categories made with a chestui.<br>
[![](https://poggit.pmmp.io/shield.state/ShopUI)](https://poggit.pmmp.io/p/ShopUI)[![HitCount](http://hits.dwyl.io/Todo56/ShopUI.svg)](http://hits.dwyl.io/Todo56/ShopUI)
## Commands
The main and only command is /shop.
## Contact
If you find any bug please open a github issue.
## Dependecies
The only dependency is economy API.
## Features
- You can select the type of chest (single or double).
- Enchantments per items.
- Custom lore for categories.
- Up to 52 items for category.
- Select whether the item's name should be kept.

## Example config
```
hopname: Select a Category #Sets the shop name
shoptype: single #single or double
categories: # Categories or your shop
  potions: # Category id
    name: §r§2Potions # Name of the category in the shop
    type: double #single or double
    item: 373 # Id of the item that represents the category
    meta: 0 # Meta of the item that represents the category
    lore: # Lore of the item
      - Select Me!
      - Just some normal and splash potions :D
    items: # List of items
      - name: Normal Potion # Name of the item (required)
        id: 373 # id of the item (required)
        meta: 0 # meta of the item (required)
        cost: 100 # cost of the item (required)
        amount: 1 # amount of the item (required)
        enchantments: # enchantments of the item (optional)
          - "sharpness:120"
          - "efficiency:5"
        keepname: true # Whether the item will keep the name specified (optional) (bool)
      - name: §dRegeneration I Potion
        id: 373
        meta: 28
        cost: 100
        amount: 1
      - name: §dRegeneration II Potion
        id: 373
        meta: 30
        cost: 100
        amount: 1
```
