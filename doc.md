## Implementačná dokumentácia k 1. úlohe do IPP 2021/2022
Meno a priezvisko: `Samuel Šulo`  
Login: `xsulos00`

### Analyzátor kódu v IPPcode22
Skript implementovaný v `parse.php` načíta zo štandardného vstupu po riadkoch zdrojový kód IPPcode22. V prípade výskytu
parametru `--help` na vstupe vypíše nápovedu na štandardný výstup.

## Implementácia
### parser.php
Riadky sú kontrolované pomocou regulárnych výrazov.
V prvom rade sa kontroluje či je na začiatku kódu okrem komentárov a prázdnych riadkov hlavička.
Následne pomocou `foreach` cyklíme cez asociatívne pole s inštrukciami a porovnávame s prípadnou inštrukciou na riadku.
Ak má inštrukcia argumenty, tak kontrolujeme či nasleduje za ňou aspoň jeden biely znak, ak nie vypíšeme chybu.
Potom prehľadáme operandy danej inštrukcie a poskladáme z nich regulárny výraz.
Výraz porovnáme s operandami na riadku a ak sa našli zhody tak ich uložíme do poľa, keď nie tak vyskočíme z cyklu.
Zhody v poli prehľadáme a neprázdne reťazce uložíme do nového poľa.
Cez nové operandy preiterujeme a vypíšeme na štandardný výstup xml reprezentáciu.
V prípade ak bol operand rovný neterminálu `symb` tak ho porovnáme s regulárnym výrazom premennej a vyhodnotíme
či sa jedná o premennú alebo konštantu.

### class Regex_constants
V triede sú vytvorené regulárne výrazy pre konštanty, premenné, identifikátory, typy, komentáre atd..

### class Errors
V triede sú definované všetky návratové kódy pre analyzátor.
