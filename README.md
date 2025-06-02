# Quiz Patente - Portale Web

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.x-orange.svg)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

Portale web completo per l'apprenadimento e la preparazione agli esami teorici della patente di guida, costruito con Laravel 12 e Filament v3.

## 📋 Panoramica

Il portale Quiz Patente è una piattaforma moderna e intuitiva che permette agli utenti di:

- **Studiare la teoria** attraverso contenuti organizzati per argomenti
- **Esercitarsi con quiz** ministeriali e personalizzati
- **Monitorare i progressi** con statistiche dettagliate
- **Ripassare gli errori** per migliorare le performance
- **Simulare l'esame** con condizioni realistiche

## ✨ Caratteristiche Principali

### 🎯 Area Studio
- **Dashboard personalizzata** con statistiche e progressi
- **Quiz ministeriali** con 40 domande casuali
- **Quiz per argomento** per studio mirato
- **Simulazioni d'esame** con timer e condizioni reali
- **Ripasso errori** intelligente basato sulle performance

### 📚 Sistema Teoria
- Contenuti organizzati per **categorie e argomenti**
- **Spiegazioni dettagliate** per ogni domanda
- **Supporto multimedia** (immagini, video, audio)
- **Progress tracking** per argomento
- **Ricerca avanzata** nei contenuti

### 📊 Analytics e Progressi
- **Statistiche dettagliate** su performance e tempo
- **Tracking errori** con analisi delle difficoltà
- **Grafici di progresso** temporali
- **Percentuali di successo** per argomento
- **Sistema di achievement** e streak giornalieri

### ⚙️ Gestione Account
- **Profilo utente** personalizzabile
- **Impostazioni avanzate** (notifiche, tema, preferenze)
- **Cronologia quiz** completa
- **Esportazione dati** e report

## 🛠 Tecnologie Utilizzate

### Backend
- **Laravel 11** - Framework PHP
- **Filament v3** - Admin Panel e UI Components
- **MySQL/MariaDB** - Database relazionale
- **PHP 8.2+** - Linguaggio di programmazione

### Frontend
- **Livewire 3** - Componenti reattivi
- **Alpine.js** - JavaScript framework
- **Tailwind CSS** - Utility-first CSS
- **Blade Templates** - Template engine

### DevOps & Tools
- **Docker** - Containerizzazione
- **Composer** - Dependency manager
- **NPM** - Package manager frontend
- **Git** - Version control

## 📋 Requisiti di Sistema

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 18.x
- **MySQL** >= 8.0 o **MariaDB** >= 10.4
- **Docker** (opzionale, ma raccomandato)

## 🚀 Installazione

### Metodo 1: Docker (Raccomandato)

```bash
# Clone del repository
git clone https://github.com/your-username/quiz-patente.git
cd quiz-patente

# Avvio dei container
docker-compose up -d

# Installazione dipendenze
docker exec -it quiz-patente-app composer install
docker exec -it quiz-patente-app npm install

# Setup ambiente
docker exec -it quiz-patente-app cp .env.example .env
docker exec -it quiz-patente-app php artisan key:generate

# Migrazione database
docker exec -it quiz-patente-app php artisan migrate --seed

# Build assets
docker exec -it quiz-patente-app npm run build
```

### Metodo 2: Installazione Locale

```bash
# Clone del repository
git clone https://github.com/your-username/quiz-patente.git
cd quiz-patente

# Installazione dipendenze
composer install
npm install

# Configurazione ambiente
cp .env.example .env
php artisan key:generate

# Configurazione database nel file .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=quiz_patente
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Migrazione e seeding
php artisan migrate --seed

# Build assets
npm run build

# Avvio server di sviluppo
php artisan serve
```

## ⚙️ Configurazione

### Configurazione Filament

Il progetto utilizza un panel Filament personalizzato configurato in `app/Providers/Filament/QuizpatentePanelProvider.php`:

```php
->id('quizpatente')
->path('')
->colors(['primary' => Color::Orange])
->navigationGroups(['Area Studio', 'Account'])
```

### Configurazione Database

Le migration creano la seguente struttura:

- **categories** - Categorie principali degli argomenti
- **topics** - Argomenti specifici di studio
- **questions** - Domande del quiz con risposte
- **theory_contents** - Contenuti teorici
- **quiz_sessions** - Sessioni di quiz degli utenti
- **quiz_answers** - Risposte date nei quiz
- **user_topic_progress** - Progressi per argomento
- **user_errors** - Tracking degli errori
- **user_settings** - Impostazioni personalizzate

### Seeding dei Dati

```bash
# Seeding completo (categories, topics, questions)
php artisan db:seed

# Seeding specifico
php artisan db:seed --class=CategoriesSeeder
php artisan db:seed --class=TopicsSeeder
php artisan db:seed --class=QuestionsSeeder
```

## 📁 Struttura del Progetto

```
app/
├── Filament/
│   ├── Resources/          # Resources per gestione dati
│   ├── Pages/             # Custom pages
│   │   ├── Account/       # Profilo e impostazioni
│   │   ├── Quiz/          # Interfacce quiz
│   │   ├── Theory/        # Sezione teoria
│   │   └── Progress/      # Statistiche e progressi
│   └── Widgets/           # Dashboard widgets
├── Models/                # Eloquent models
├── Http/
│   └── Controllers/       # Controllers API e custom
└── Services/              # Business logic services

database/
├── migrations/            # Database migrations
├── seeders/              # Data seeders
└── factories/            # Model factories

resources/
├── views/
│   └── filament/         # Custom Filament views
├── css/                  # Stylesheets
└── js/                   # JavaScript files
```

## 🎮 Utilizzo

### Accesso al Portale

1. **Registrazione**: Crea un nuovo account utente
2. **Login**: Accedi con le tue credenziali
3. **Dashboard**: Visualizza statistiche e progressi
4. **Studio**: Naviga tra teoria e quiz

### Modalità Quiz Disponibili

#### Quiz Ministeriale
- 40 domande casuali da tutti gli argomenti
- Timer di 30 minuti
- Necessario 80% per superare (32/40 corrette)

#### Quiz per Argomento
- Domande specifiche per argomento selezionato
- Numero variabile di domande
- Focus su aree di miglioramento

#### Ripasso Errori
- Solo domande sbagliate in precedenza
- Algoritmo intelligente di prioritizzazione
- Spiegazioni dettagliate per ogni errore

### Sistema di Progressi

- **Tracking accurato** di tutte le attività
- **Statistiche dettagliate** per argomento
- **Analisi temporale** dei miglioramenti
- **Identificazione** delle aree deboli

## 🔧 Personalizzazione

### Aggiungere Nuovi Argomenti

```php
// Tramite Seeder
Topic::create([
    'category_id' => 1,
    'code' => '15',
    'name' => 'Nuovo Argomento',
    'description' => 'Descrizione dell\'argomento',
    'total_questions' => 50,
]);
```

### Personalizzare il Dashboard

Crea nuovi widget in `app/Filament/Widgets/`:

```php
class CustomStatsWidget extends BaseWidget
{
    protected static string $view = 'filament.widgets.custom-stats';
    // ...
}
```

### Aggiungere Nuove Funzionalità

1. **Crea Model** e migration
2. **Configura relazioni** Eloquent
3. **Crea Resource** Filament per gestione
4. **Implementa logica** nei Services
5. **Aggiungi test** unitari e feature

## 🧪 Testing

```bash
# Esegui tutti i test
php artisan test

# Test specifici
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Test con coverage
php artisan test --coverage
```

## 📈 Performance

### Ottimizzazioni Implementate

- **Eager Loading** per ridurre query N+1
- **Caching** intelligente per domande e progressi
- **Indexing** ottimizzato per query frequenti
- **Lazy Loading** per contenuti multimediali

### Monitoring

```bash
# Query debugging
php artisan telescope:install

# Performance monitoring
php artisan horizon:install
```

## 🚀 Deployment

### Configurazione Produzione

```bash
# Ottimizzazione per produzione
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Asset building
npm run production
```

### Docker Production

```dockerfile
# Dockerfile ottimizzato per produzione
FROM php:8.2-fpm-alpine
# ... configurazione ottimizzata
```

## 🤝 Contribuire

1. **Fork** del repository
2. **Crea branch** feature (`git checkout -b feature/AmazingFeature`)
3. **Commit** modifiche (`git commit -m 'Add AmazingFeature'`)
4. **Push** al branch (`git push origin feature/AmazingFeature`)
5. **Apri Pull Request**

### Guidelines

- Segui **PSR-12** coding standards
- Scrivi **test** per nuove funzionalità
- Documenta **API** e metodi pubblici
- Usa **conventional commits**

## 📝 Roadmap

### Version 1.0
- [x] Sistema base quiz e teoria
- [x] Dashboard e progressi
- [x] Gestione account e impostazioni
- [ ] Sistema di notifiche
- [ ] API mobile

### Version 2.0
- [ ] Modalità multiplayer
- [ ] Sistema di classifiche
- [ ] Contenuti video avanzati
- [ ] AI-powered recommendations
- [ ] Progressive Web App

## 📄 Licenza

Questo progetto è rilasciato sotto licenza [MIT](LICENSE).

## 👥 Team

- **Lead Developer**: [Your Name](https://github.com/vincenzopirozzideveloper)
- **UI/UX Designer**: [Designer Name](https://github.com/designer)

## 📞 Supporto

- **Email**: support@quizpatente.it
- **GitHub Issues**: [Report Bug](https://github.com/vincenzopirozzideveloper/quiz-patente/issues)
- **Documentation**: [Wiki](https://github.com/vincenzopirozzideveloper/quiz-patente/wiki)

---

**Made with ❤️ and ☕ in Italy**