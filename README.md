# Medijpratiba.lv WordPress spraudņa ielādes instrukcijas

Lai lietotu **[Medijpratiba.lv spēles WordPress spraudni](https://github.com/medijpratiba/medijpratibai)**, nepieciešams

- jau esoša WordPress mājas lapa;
- iespēju ielādēt saturu mājas lapā. Ieteicams lietot standarta "[WordPress Importer](https://wordpress.org/plugins/wordpress-importer/)" (vai skatīt mapi "wordpress-importer");
- ielādēt papildus spraudni "[Meta Box – WordPress Custom Fields Framework](https://wordpress.org/plugins/meta-box/)" (vai skatīt mapi "meta-box"). "[Advanced Custom Fields (ACF)](https://wordpress.org/plugins/advanced-custom-fields/)" atbalsts var tikt pievienots nākotnē;

## Pielāgotie laukumi

Ja nav vēlme vai iespēja ieladēt papildus spraudņus, tad spraudnis izmanto šādus _pielāgotos laukus_ "**mpquestions**" rakstu tipam:

### Pamata lauki

- `mpc_nrpk` (_skaitlis_), kurš nosaka, kuram laukumam jautājums atbilst. Iespējamie skaitļi ir **no 1 līdz 23**
- `mpc_solis` (_skaitlis_), kurš nosaka, cik laukumus uz priekšu spēlētājs dodas. Iespējamie skaitļi ir **no 1 līdz 3**
- `mpc_atbildes_y` (_teksts_) - pareizā atbilde.
- `mpc_atbildes_n` (_teksts_) - Nepareizās atbildes. Var pievienot vairākus laukumus. Spēle atbalsta **2** nepareizo atbilžu variantus, lai uz mazākiem ekrāniem būtu iespēja apskatīt visus 3.
- `mpc_paskaidrojums` (_paplašinātais teksts_ (WYSIWYG, textarea)) - paskaidrojums atbildei. Pieļaujams HTML.

### Papildus lauki

Papildus lauki pagaidām nav iekļauti, bet var ietvert šādus (**rezervētie laukumu nosaukumi**):

(nepersonalizētai) **Statistikai**

- `mpc_stats_skatijumi` (_skaitlis_)- cik bieži jautājums ir atvērts
- `mpc_stats_atbilde_y` (_skaitlis_)- cik bieži atbildēts pareizi
- `mpc_stats_atbilde_n` (_skaitlis_)- cik bieži atbildēts nepareizi

Ieteicams **neizmantot** `mpc_stats_` kā lauka nosaukumu paplašinot spraudni.

## Iepriekš sagatavotie jautājumi

Spēles veidotāju iepriekš sagatavotie jautājumi un atbilde ir pieejami mapē `xml`, kur tie ir sagatavoti standrta WordPress datu importēšanas formātā:

- aktuālā versija [20201118-01-medijpratibalv-spele-demo.xml](xml/20201118-01-medijpratibalv-spele-demo.xml)
