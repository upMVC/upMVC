# ReactHMR - Visual Architecture Guide

## 🎯 The Big Picture

```
┌─────────────────────────────────────────────────────────────┐
│                                                             │
│                    ReactHMR System                          │
│                                                             │
│  "Hot Module Reload WITHOUT Webpack"                        │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔄 System Flow

```
┌──────────────────────┐
│   Developer          │
│                      │
│  1. Edits View.php   │
│  2. Saves file       │
└──────┬───────────────┘
       │
       │ File saved
       │
       ▼
┌──────────────────────┐
│   File System        │
│                      │
│  filemtime() changes │
└──────┬───────────────┘
       │
       │ PHP checks every 1s
       │
       ▼
┌──────────────────────┐
│   PHP Controller     │
│                      │
│  getFileHashes()     │
│  detects change      │
│                      │
│  Sends SSE event     │
└──────┬───────────────┘
       │
       │ SSE Stream
       │ event: reload
       │
       ▼
┌──────────────────────┐
│   Browser            │
│                      │
│  EventSource         │
│  receives 'reload'   │
│                      │
│  location.reload()   │
└──────┬───────────────┘
       │
       │ Page refreshes
       │
       ▼
┌──────────────────────┐
│   Updated Page       │
│                      │
│  Developer sees      │
│  changes! ✨         │
└──────────────────────┘

Total time: ~1.5 seconds
```

---

## 🏗 Component Architecture

```
┌────────────────────────────────────────────────────────────┐
│                      Browser Window                         │
│                                                            │
│  ┌──────────────────────────────────────────────────────┐ │
│  │                    HMR Status                        │ │
│  │  🟢 Connected      [Fixed bottom-right]              │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                            │
│  ┌──────────────────────────────────────────────────────┐ │
│  │                    Hero Section                       │ │
│  │              🔥 Hot Module Reload                     │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                            │
│  ┌────────────────┐  ┌────────────────┐  ┌──────────────┐│
│  │   Counter      │  │  User Table    │  │   Stats      ││
│  │  (Preact)      │  │  (PHP Data)    │  │ (PHP Data)   ││
│  │                │  │                │  │              ││
│  │  [- ] 0  [+]   │  │  ID │ Name     │  │  Total: 5    ││
│  └────────────────┘  └────────────────┘  └──────────────┘│
│                                                            │
│  ┌──────────────────────────────────────────────────────┐ │
│  │              Todo App (External File)                 │ │
│  │  [Add todo...]                             [Add]      │ │
│  │  ☑ Learn HMR                                [Delete]  │ │
│  │  ☐ Build apps                               [Delete]  │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                            │
│  ┌──────────────────────────────────────────────────────┐ │
│  │                  Vue.js Example                       │ │
│  │            Hello from Vue.js!                         │ │
│  │            [Clicked 0 times]                          │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                            │
└────────────────────────────────────────────────────────────┘

        ▲                                      ▲
        │                                      │
        │ ES Modules                           │ SSE Connection
        │ (import maps)                        │ (EventSource)
        │                                      │
    ┌───┴──────────┐                    ┌─────┴──────────┐
    │ View.php     │                    │ Controller.php │
    │              │                    │                │
    │ - Components │                    │ - hmrStream()  │
    │ - Import Map │                    │ - fileWatch()  │
    └──────────────┘                    └────────────────┘
```

---

## 🔌 Connection Lifecycle

```
Browser Loads Page
       │
       ▼
┌─────────────────┐
│ Create          │
│ EventSource     │──────► Connect to /reacthmr/hmr
└────────┬────────┘
         │
         │ Connection established
         │
         ▼
┌─────────────────┐
│ Status: 🟢      │
│ Connected       │
└────────┬────────┘
         │
         │ Heartbeat every 10s
         │ ": heartbeat\n\n"
         │
         ▼
┌─────────────────┐
│ File Changed?   │
│ Check every 1s  │
└────────┬────────┘
         │
         ├─ NO ──► Continue checking
         │
         └─ YES ──►┌─────────────────┐
                   │ Send SSE Event  │
                   │ event: reload   │
                   │ data: {...}     │
                   └────────┬────────┘
                            │
                            ▼
                   ┌─────────────────┐
                   │ Status: 🔵      │
                   │ Reloading...    │
                   └────────┬────────┘
                            │
                            │ Fade effect
                            │ opacity: 0.5
                            │
                            ▼
                   ┌─────────────────┐
                   │ location.reload │
                   └─────────────────┘
```

---

## 📁 File Watch Mechanism

```
┌─────────────────────────────────────────────────────┐
│              getFileHashes()                        │
│                                                     │
│  For each path in $watchPaths:                     │
│                                                     │
│    ┌──────────────────┐                            │
│    │ Is File?         │                            │
│    └────┬────────┬────┘                            │
│         │        │                                  │
│      YES│        │NO                                │
│         │        │                                  │
│         ▼        ▼                                  │
│  ┌──────────┐  ┌─────────────────┐                │
│  │ filemtime│  │ getFilesRecursive│                │
│  │ ($file)  │  │ ($dir)           │                │
│  └────┬─────┘  └────┬────────────┘                │
│       │             │                               │
│       │             │ For each file:                │
│       │             │   if (ext in [php,js,html])   │
│       │             │     get filemtime()           │
│       │             │                               │
│       ▼             ▼                               │
│  ┌────────────────────┐                            │
│  │  Concat all times  │                            │
│  │  md5($times)       │                            │
│  └────────┬───────────┘                            │
│           │                                         │
│           ▼                                         │
│  ┌────────────────────┐                            │
│  │  Return hash       │                            │
│  │  "abc123def456..." │                            │
│  └────────────────────┘                            │
│                                                     │
│  Compare with previous hash:                       │
│    if (current !== previous) → Send reload         │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

## 🎨 Component Data Flow

```
PHP Backend                              Browser
┌──────────────┐                    ┌──────────────┐
│  Model.php   │                    │   View.php   │
│              │                    │              │
│ getSample    │                    │ <script      │
│ Users()      │                    │   id="data"> │
│              │                    │              │
│ return [     │────────────────────►│ <?php echo   │
│   {id:1,     │   JSON.encode()    │  json_encode │
│    name:...} │                    │  ($data) ?>  │
│ ]            │                    │ </script>    │
└──────────────┘                    └──────┬───────┘
                                           │
                                           │ Parse JSON
                                           │
                                           ▼
                                    ┌──────────────┐
                                    │  Component   │
                                    │              │
                                    │ const data = │
                                    │  JSON.parse  │
                                    │  (el.text)   │
                                    │              │
                                    │ render(data) │
                                    └──────────────┘
```

---

## 🔄 SSE Stream Format

```
HTTP Request:
GET /reacthmr/hmr HTTP/1.1
Accept: text/event-stream

HTTP Response Headers:
Content-Type: text/event-stream
Cache-Control: no-cache
Connection: keep-alive

Stream Content:
┌──────────────────────────────────┐
│ event: reload                    │ ← Event name
│ data: {"timestamp":1234,"..."}   │ ← Event data
│                                  │ ← Empty line (message boundary)
├──────────────────────────────────┤
│                                  │
│ (wait 10 seconds)                │
│                                  │
├──────────────────────────────────┤
│ : heartbeat                      │ ← Comment (keep-alive)
│                                  │
├──────────────────────────────────┤
│                                  │
│ (file changes detected)          │
│                                  │
├──────────────────────────────────┤
│ event: reload                    │
│ data: {"timestamp":1235,"..."}   │
│                                  │
└──────────────────────────────────┘

Browser receives and triggers:
eventSource.addEventListener('reload', callback)
```

---

## 🎯 Timeline Diagram

```
0ms ────────────────────────────────────────────────────────────► 1550ms
│                                                                    │
│  Save                                                              │
│  File                                                              │
│   │                                                                │
│   │                                                                │
│   ▼                                                                │
│  Sleep                                                             │
│  1000ms ────────────────────────────────────────►                 │
│                                                   │                │
│                                                   │                │
│                                                   ▼                │
│                                                  Detect            │
│                                                  Change            │
│                                                   │                │
│                                                   │                │
│                                                   ▼                │
│                                                  Send              │
│                                                  SSE               │
│                                                  50ms ──►          │
│                                                          │         │
│                                                          │         │
│                                                          ▼         │
│                                                         Receive    │
│                                                         Event      │
│                                                          │         │
│                                                          │         │
│                                                          ▼         │
│                                                         Fade       │
│                                                         300ms ───► │
│                                                                  │ │
│                                                                  ▼ │
│                                                                Reload
│                                                                200ms
│                                                                  │
│                                                                  ▼
│                                                                Done!
│                                                                  
Total: ~1.5 seconds from save to visible change
```

---

## 🏗 Module Structure

```
reacthmr/
│
├── Controller.php          280 lines
│   │
│   ├── display()          ← Route dispatcher
│   ├── index()            ← Main page
│   ├── hmrStream()        ← SSE endpoint ★
│   ├── getFileHashes()    ← Change detection ★
│   ├── getFilesRecursive() ← Directory scanning
│   └── serveComponent()   ← Serve TodoApp.js
│
├── Model.php               60 lines
│   │
│   ├── getSampleUsers()   ← Demo data
│   ├── getStats()         ← Dashboard stats
│   └── getTodos()         ← Todo items
│
├── View.php                580 lines
│   │
│   ├── render()           ← Main render
│   ├── importMaps()       ← ES module config ★
│   ├── hmrClient()        ← Browser SSE client ★
│   ├── styles()           ← Component CSS
│   ├── preactCounter()    ← Counter component
│   ├── preactUsers()      ← User table
│   ├── preactStats()      ← Stats dashboard
│   └── vueExample()       ← Vue.js demo
│
├── routes/Routes.php       30 lines
│   │
│   └── register()         ← Register 3 routes
│
├── components/
│   │
│   └── TodoApp.js         90 lines
│       │
│       └── TodoApp()      ← External component ★
│
└── README.md              550 lines
    └── Complete documentation

Total: ~1,590 lines
★ = Core HMR functionality
```

---

## 🎮 Status Indicator States

```
┌────────────────────────────────────────────┐
│            HMR Status States               │
└────────────────────────────────────────────┘

🟢 CONNECTED
┌──────────────────┐
│  🟢 HMR Connected│
└──────────────────┘
└─► Normal operation
    └─► Watching files
        └─► Sending heartbeats


🟠 DISCONNECTED
┌──────────────────────┐
│  🟠 HMR Reconnecting...│
└──────────────────────┘
└─► Connection lost
    └─► Auto-reconnect in 2s
        └─► Retry connection


🔵 RELOADING
┌──────────────────┐
│  🔵 Reloading... │
└──────────────────┘
└─► Change detected
    └─► Fade effect starting
        └─► About to reload


🔴 ERROR
┌──────────────────────┐
│  🔴 HMR Not Supported│
└──────────────────────┘
└─► Browser doesn't support SSE
    └─► Manual refresh required
```

---

## 📊 Performance Comparison

```
┌─────────────────────────────────────────────────────────┐
│              Development Feedback Loop                  │
└─────────────────────────────────────────────────────────┘

Manual Refresh (Pattern 4):
Edit ─► Save ─► Switch to Browser ─► F5 ─► See Change
0ms     0ms     2000ms               500ms    2500ms
                                          │
                                          ▼
                                    Total: ~5s


ReactHMR (Pattern 5):
Edit ─► Save ─► Detect ─► SSE ─► Fade ─► Reload ─► See Change
0ms     0ms     1000ms    50ms    300ms   200ms    1550ms
                                                        │
                                                        ▼
                                                  Total: ~1.5s


Webpack HMR:
Edit ─► Save ─► Hot Swap ─► See Change
0ms     0ms     500ms         500ms
                          │
                          ▼
                    Total: ~0.5s


┌──────────────┬──────────┬────────────┬──────────┐
│   Method     │   Time   │   Effort   │ Build    │
├──────────────┼──────────┼────────────┼──────────┤
│ Manual (P4)  │   ~5s    │   Manual   │    No    │
│ ReactHMR     │  ~1.5s   │   Auto     │    No    │
│ Webpack      │  ~0.5s   │   Auto     │   Yes    │
└──────────────┴──────────┴────────────┴──────────┘

Conclusion: ReactHMR is 3x faster than manual,
           simpler than Webpack
```

---

## 🔧 Configuration Flow

```
┌──────────────────────────────────────────────────────┐
│            Configurable Parameters                   │
└──────────────────────────────────────────────────────┘

Watch Paths:
private $watchPaths = [
    'modules/reacthmr/templates/',  ◄─── Add more paths
    'modules/reacthmr/components/', ◄─── Or specific files
];

         ▼

Check Interval:
sleep(1);  // Check every 1 second
          ▼
sleep(0.5);  // Faster: every 500ms
          ▼
sleep(2);    // Slower: every 2 seconds

         ▼

File Types:
if (in_array($ext, ['php', 'js', 'html', 'css'])) {
                    ▲
                    │
        Add more: 'json', 'xml', 'md'

         ▼

Fade Duration:
setTimeout(() => location.reload(), 300);
                                     ▲
                                     │
                              Adjust: 0 (instant)
                                      or 500 (slower)

         ▼

Environment Check:
<?php if (ENVIRONMENT === 'development'): ?>
          ▲
          │
    Production: auto-disabled
```

---

## 🎓 Learning Map

```
┌────────────────────────────────────────────────────────┐
│         What Developers Learn from ReactHMR           │
└────────────────────────────────────────────────────────┘

1. Server-Sent Events (SSE)
   │
   ├─► EventSource API
   ├─► Text/event-stream format
   ├─► Keep-alive (heartbeat)
   └─► Auto-reconnect

2. PHP File Watching
   │
   ├─► filemtime() function
   ├─► Recursive directory scan
   ├─► Hash-based change detection
   └─► While loop for continuous check

3. ES Modules
   │
   ├─► Import Maps
   ├─► HTM (JSX alternative)
   ├─► Dynamic imports
   └─► Framework-agnostic

4. Real-Time Communication
   │
   ├─► Push vs Pull
   ├─► Long-polling alternative
   ├─► WebSocket comparison
   └─► Connection management

5. PHP ↔ JS Data Flow
   │
   ├─► JSON serialization
   ├─► Component hydration
   ├─► State management
   └─► Data synchronization
```

---

## 🚀 Quick Start Flowchart

```
                    START
                      │
                      ▼
        ┌──────────────────────────┐
        │  Install upMVC           │
        └────────────┬─────────────┘
                     │
                     ▼
        ┌──────────────────────────┐
        │  Visit /reacthmr         │
        └────────────┬─────────────┘
                     │
                     ▼
        ┌──────────────────────────┐
        │  See HMR demo page       │
        │  Status: 🟢 Connected    │
        └────────────┬─────────────┘
                     │
                     ▼
        ┌──────────────────────────┐
        │  Open View.php           │
        │  in your editor          │
        └────────────┬─────────────┘
                     │
                     ▼
        ┌──────────────────────────┐
        │  Change text/code        │
        └────────────┬─────────────┘
                     │
                     ▼
        ┌──────────────────────────┐
        │  Save file (Ctrl+S)      │
        └────────────┬─────────────┘
                     │
                     ▼
        ┌──────────────────────────┐
        │  Watch browser!          │
        │  Status: 🔵 Reloading... │
        └────────────┬─────────────┘
                     │
                     │ ~1.5s
                     ▼
        ┌──────────────────────────┐
        │  See your changes! ✨    │
        │  Status: 🟢 Connected    │
        └────────────┬─────────────┘
                     │
                     ▼
                   DONE!
          
       Repeat: Edit → Save → See
```

---

**Visual Architecture Guide Complete** ✨

This diagram-based document provides a visual understanding of:
- System flow
- Component architecture
- Connection lifecycle
- File watching mechanism
- Data flow
- SSE format
- Timeline breakdown
- Module structure
- Status states
- Performance comparison
- Configuration options
- Learning map
- Quick start flow

Perfect for visual learners! 🎨
