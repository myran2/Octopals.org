<?php
abstract class PlayerSpec {
    const Warrior = 0x1;
    const Paladin = 0x2;
    const DeathKnight = 0x4;
    const Shaman = 0x8;
    const Hunter = 0x10;
    const Druid = 0x20;
    const Rogue = 0x40;
    const DemonHunter = 0x80;
    const Monk = 0x100;
    const Warlock = 0x200;
    const Priest = 0x400;
    const Mage = 0x800;
    const Evoker = 0x1000;
}

abstract class PlayerRoles {
    const Tank = 0x1;
    const Healer = 0x2;
    const Melee = 0x4;
    const Range = 0x8;
}

$classIdToClassMask = [
    1 => PlayerSpec::Warrior,
    2 => PlayerSpec::Paladin,
    3 => PlayerSpec::Hunter,
    4 => PlayerSpec::Rogue,
    5 => PlayerSpec::Priest,
    6 => PlayerSpec::DeathKnight,
    7 => PlayerSpec::Shaman,
    8 => PlayerSpec::Mage,
    9 => PlayerSpec::Warlock,
    10 => PlayerSpec::Monk,
    11 => PlayerSpec::Druid,
    12 => PlayerSpec::DemonHunter,
    13 => PlayerSpec::Evoker,
];

abstract class LootResponse {
    const Major = 1;
    const Minor = 2;
    const Socket = 3;
    const Offspec = 4;
    const DontNeed = 5;
}

abstract class MythicItemBonusIds {
    const VaultOfTheIncarnates = '7977:8807';
}

?>
