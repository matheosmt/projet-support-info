<?php
declare(strict_types=1);
class Database {
    private static ?PDO $pdo = null;
    public static function get(): PDO {
        if (self::$pdo) return self::$pdo;
        $file = __DIR__ . '/../../storage/certihub.sqlite';
        $first = !file_exists($file);
        self::$pdo = new PDO('sqlite:' . $file);
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        self::$pdo->exec('PRAGMA foreign_keys = ON');
        $schema = file_get_contents(__DIR__ . '/../../database/schema.sql');
        if ($first) {
            self::$pdo->exec($schema);
            self::seed(self::$pdo);
        } else {
            self::migrate(self::$pdo);
        }
        return self::$pdo;
    }
    private static function migrate(PDO $pdo): void {
        $cols = [];
        foreach ($pdo->query('PRAGMA table_info(certifications)') as $row) $cols[$row['name']] = true;
        if (!isset($cols['difficulty'])) $pdo->exec("ALTER TABLE certifications ADD COLUMN difficulty TEXT NOT NULL DEFAULT 'intermediaire'");
        if (!isset($cols['price_eur'])) $pdo->exec("ALTER TABLE certifications ADD COLUMN price_eur INTEGER NOT NULL DEFAULT 0");
        self::syncCertificationMeta($pdo);
        self::insertMissingCertifications($pdo);
        self::insertMissingTracks($pdo);
    }
    private static function insertMissingCertifications(PDO $pdo): void {
        $existing = [];
        foreach ($pdo->query('SELECT name FROM certifications') as $row) $existing[$row['name']] = true;
        $stmt = $pdo->prepare('INSERT INTO certifications(vendor,name,domain,summary,prerequisites,featured,certified_count,difficulty,price_eur) VALUES(?,?,?,?,?,?,?,?,?)');
        foreach (self::certificationCatalog() as $c) {
            if (!isset($existing[$c[1]])) $stmt->execute($c);
        }
    }
    private static function insertMissingTracks(PDO $pdo): void {
        $tracks=[
            ['Après BTS SIO - SISR','reseau','Construire un socle solide en systèmes, réseaux, sécurité et cloud.',1],
            ['Après BTS SIO - SLAM','dev','Passer du développement applicatif aux pratiques DevOps et cloud.',2],
            ['Bachelor Développement','dev','Consolider les fondamentaux dev, Git, cloud et déploiement.',3],
            ['Bachelor Cybersécurité','cyber','Structurer une progression de la sécurité réseau jusqu’aux pratiques SOC et cloud.',4],
            ['Bachelor Cloud & DevOps','cloud','Valider Linux, réseau, cloud, conteneurs et automatisation.',5],
            ['Bachelor Data','data','Construire un socle SQL, data engineering, analytics et plateformes data.',6],
            ['Mastère IA & Machine Learning','ia','Approfondir Python, data, ML, MLOps et services IA cloud.',7],
            ['Mastère Cybersécurité','cyber','Approfondir sécurité offensive, défense, gouvernance et cloud security.',8],
            ['École d’ingénieur — Cloud & Infrastructure','cloud','Préparer des certifications avancées cloud, architecture et infrastructure.',9],
            ['Réseaux & Systèmes','systeme','Approfondir administration Linux, réseau, virtualisation et automatisation.',10],
            ['DevOps & Platform Engineering','dev','Automatiser les déploiements et industrialiser les plateformes cloud.',11],
        ];
        $stmt=$pdo->prepare('INSERT OR IGNORE INTO tracks(name,domain,description,sort_order) VALUES(?,?,?,?)');
        foreach($tracks as $t) $stmt->execute($t);
        $maps=[[1,1],[1,2],[1,4],[1,7],[1,12],[2,14],[2,15],[2,5],[2,22],[3,15],[3,21],[3,5],[3,12],[4,3],[4,6],[4,9],[4,25],[4,27],[4,34],[5,7],[5,12],[5,14],[5,17],[5,18],[5,31],[6,4],[6,20],[6,24],[6,28],[6,36],[7,20],[7,26],[7,29],[7,32],[7,37],[8,3],[8,9],[8,25],[8,30],[8,34],[8,40],[9,7],[9,17],[9,18],[9,21],[9,31],[10,4],[10,7],[10,12],[10,14],[11,5],[11,15],[11,18],[11,22],[11,31]];
        $stmt=$pdo->prepare('INSERT OR IGNORE INTO track_certifications(track_id,certification_id,position) VALUES(?,?,?)'); $pos=[];
        foreach($maps as [$tr,$ce]){$pos[$tr]=($pos[$tr]??0)+1;$stmt->execute([$tr,$ce,$pos[$tr]]);}
    }
    private static function syncCertificationMeta(PDO $pdo): void {
        $meta = self::certificationCatalog();
        $stmt = $pdo->prepare('UPDATE certifications SET difficulty=?, price_eur=? WHERE name=?');
        foreach ($meta as $c) {
            $stmt->execute([$c[7], $c[8], $c[1]]);
        }
    }
    private static function seed(PDO $pdo): void {
        $users = [
            ['Directeur', 'directeur@certihub.local', password_hash('DemoAdmin123!', PASSWORD_DEFAULT), 'admin'],
            ['Support IT', 'support@certihub.local', password_hash('DemoSupport123!', PASSWORD_DEFAULT), 'support'],
            ['Alice Dupont', 'alice@certihub.local', password_hash('DemoStudent123!', PASSWORD_DEFAULT), 'eleve'],
        ];
        $stmt=$pdo->prepare('INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,?)');
        foreach($users as $u)$stmt->execute($u);

        $certs = self::certificationCatalog();
        $stmt=$pdo->prepare('INSERT INTO certifications(vendor,name,domain,summary,prerequisites,featured,certified_count,difficulty,price_eur) VALUES(?,?,?,?,?,?,?,?,?)');
        foreach($certs as $c)$stmt->execute($c);

        $tracks=[
            ['Après BTS SIO - SISR','reseau','Construire un socle solide en systèmes, réseaux, sécurité et cloud.'],
            ['Après BTS SIO - SLAM','dev','Passer du développement applicatif aux pratiques DevOps et cloud.'],
            ['Bachelor Développement','dev','Consolider les fondamentaux dev, Git, cloud et déploiement.'],
            ['Bachelor Cybersécurité','cyber','Structurer une progression de la sécurité réseau jusqu’aux pratiques SOC et cloud.'],
            ['Bachelor Cloud & DevOps','cloud','Valider Linux, réseau, cloud, conteneurs et automatisation.'],
            ['Bachelor Data','data','Construire un socle SQL, data engineering, analytics et plateformes data.'],
            ['Mastère IA & Machine Learning','ia','Approfondir Python, data, ML, MLOps et services IA cloud.'],
            ['Mastère Cybersécurité','cyber','Approfondir sécurité offensive, défense, gouvernance et cloud security.'],
            ['École d’ingénieur — Cloud & Infrastructure','cloud','Préparer des certifications avancées cloud, architecture et infrastructure.'],
            ['Réseaux & Systèmes','systeme','Approfondir administration Linux, réseau, virtualisation et automatisation.'],
            ['DevOps & Platform Engineering','dev','Automatiser les déploiements et industrialiser les plateformes cloud.'],
        ];
        $stmt=$pdo->prepare('INSERT INTO tracks(name,domain,description,sort_order) VALUES(?,?,?,?)');
        foreach(array_values($tracks) as $i=>$t){$stmt->execute([$t[0],$t[1],$t[2],$i+1]);}

        $maps=[[1,1],[1,2],[1,4],[1,7],[1,12],[2,14],[2,15],[2,5],[2,22],[3,15],[3,21],[3,5],[3,12],[4,3],[4,6],[4,9],[4,25],[4,27],[4,34],[5,7],[5,12],[5,14],[5,17],[5,18],[5,31],[6,4],[6,20],[6,24],[6,28],[6,36],[7,20],[7,26],[7,29],[7,32],[7,37],[8,3],[8,9],[8,25],[8,30],[8,34],[8,40],[9,7],[9,17],[9,18],[9,21],[9,31],[10,4],[10,7],[10,12],[10,14],[11,5],[11,15],[11,18],[11,22],[11,31]];
        $stmt=$pdo->prepare('INSERT INTO track_certifications(track_id,certification_id,position) VALUES(?,?,?)'); $pos=[];
        foreach($maps as [$tr,$ce]){$pos[$tr]=($pos[$tr]??0)+1;$stmt->execute([$tr,$ce,$pos[$tr]]);}

        $trainings=[
            [1,'AWS Cloud Foundations','cloud','Déployer une première architecture AWS et comprendre IAM, compute, storage et réseau.','Débutant','12h'],
            [2,'CCNA Lab : VLAN & Routing','reseau','Travaux pratiques sur Packet Tracer : VLAN, trunking, inter-VLAN, OSPF et dépannage.','Intermédiaire','18h'],
            [3,'Security+ Bootcamp','cyber','Menaces, sécurité réseau, IAM, cryptographie et réponse à incident.','Intermédiaire','16h'],
            [12,'Linux Admin Essentials','systeme','Shell, utilisateurs, permissions, services, réseau et supervision sous Linux.','Débutant','10h'],
            [15,'DevOps Foundations','dev','Git, CI/CD, conteneurs et déploiement reproductible.','Intermédiaire','14h'],
            [20,'Data & SQL Foundations','data','Modélisation, SQL, nettoyage et premières pipelines de données.','Débutant','15h'],
        ];
        $stmt=$pdo->prepare('INSERT INTO trainings(certification_id,title,domain,description,level,duration) VALUES(?,?,?,?,?,?)'); foreach($trainings as $t)$stmt->execute($t);
        $news=[
            ['Cloud','FinOps : pourquoi les écoles forment aussi aux coûts cloud','Comprendre les leviers de maîtrise des coûts permet de rendre les projets cloud plus réalistes.','2026-09-18'],
            ['Cyber','Zero Trust : 5 notions à connaître avant une alternance cyber','Identité, segmentation, moindre privilège, contrôle continu et visibilité sont au cœur des architectures modernes.','2026-09-12'],
            ['IA','RAG : le concept à connaître pour les projets IA d’étudiants','Retrieval-Augmented Generation permet de connecter un LLM à une base documentaire contextualisée.','2026-09-06'],
            ['Réseau','IPv6 et automatisation réseau : les compétences qui montent','Le scripting et l’automatisation complètent les fondamentaux réseau traditionnels.','2026-08-30'],
        ];
        $stmt=$pdo->prepare('INSERT INTO news(category,title,excerpt,published_at) VALUES(?,?,?,?)');foreach($news as $n)$stmt->execute($n);
        $stmt=$pdo->prepare('INSERT INTO user_certifications(user_id,certification_id,status,progress,updated_at) VALUES(?,?,?,?,datetime("now"))');
        $stmt->execute([3,1,'en_cours',68]);$stmt->execute([3,3,'obtenue',100]);$stmt->execute([3,2,'a_planifier',15]);
    }
    private static function certificationCatalog(): array {
        return [
            ['AWS','AWS Certified Cloud Practitioner','cloud','Comprendre les fondamentaux AWS, la sécurité, les services clés et la tarification cloud.','Avoir de bonnes bases réseau, système et cloud. Aucune expérience AWS obligatoire.',1,12480,'debutant',100],
            ['Cisco','CCNA','reseau','Valider les fondamentaux réseau : adressage IP, switching, routing et sécurité de base.','Bases TCP/IP, VLAN, routage et administration réseau.',1,18420,'intermediaire',300],
            ['CompTIA','Security+','cyber','Consolider les fondamentaux cybersécurité, les menaces, l’IAM et la sécurité réseau.','Connaissances réseau et systèmes recommandées.',1,9650,'intermediaire',392],
            ['Microsoft','Azure Administrator Associate','cloud','Administrer les services Azure, l’identité, le réseau et les machines virtuelles.','Bases Azure, identité, réseaux et machines virtuelles.',1,7210,'intermediaire',165],
            ['Google Cloud','Associate Cloud Engineer','cloud','Déployer et exploiter des workloads sur Google Cloud.','Linux, réseau et notions GCP recommandés.',0,6140,'intermediaire',200],
            ['ISC2','Certified in Cybersecurity','cyber','Point d’entrée accessible pour acquérir les fondamentaux de la cybersécurité.','Aucun prérequis technique formel.',0,5400,'debutant',0],
            ['Linux Foundation','LFCS','systeme','Certification pratique en administration Linux et gestion des services système.','Bases Linux en ligne de commande et administration système.',0,3380,'intermediaire',395],
            ['PECB','ISO/IEC 27001 Foundation','cyber','Comprendre les principes d’un SMSI et les bases de la norme ISO 27001.','Aucune expérience technique obligatoire.',0,4210,'debutant',250],
            ['Oracle','Java SE Developer','dev','Valider les compétences Java modernes, la POO, les collections et les API.','Bonne pratique Java et programmation objet.',0,5310,'intermediaire',245],
            ['Databricks','Data Engineer Associate','data','Certification orientée data engineering, pipelines et Lakehouse.','SQL, Python et notions de data engineering.',0,2870,'intermediaire',200],
            ['Microsoft','Azure Fundamentals (AZ-900)','cloud','Découvrir les concepts cloud, les services Azure, la sécurité et la tarification.','Aucun prérequis technique formel.',1,15200,'debutant',99],
            ['AWS','AWS Certified Solutions Architect - Associate','cloud','Concevoir des architectures AWS robustes, sécurisées et résilientes.','Bonnes bases AWS et expérience pratique recommandée.',1,10300,'intermediaire',150],
            ['AWS','AWS Certified Developer - Associate','dev','Développer, déployer et déboguer des applications sur AWS.','Bases de développement et services AWS.',0,6900,'intermediaire',150],
            ['AWS','AWS Certified SysOps Administrator - Associate','systeme','Exploiter, superviser et automatiser des workloads AWS.','Bases administration système et AWS.',0,4700,'intermediaire',150],
            ['Microsoft','Azure Developer Associate','dev','Développer des solutions cloud avec les services Azure et les pratiques DevOps.','Expérience en développement et services Azure.',0,5100,'intermediaire',165],
            ['Microsoft','Azure Security Engineer Associate','cyber','Sécuriser les identités, données, réseaux et workloads Azure.','Bases Azure et sécurité.',0,3300,'avance',165],
            ['Google Cloud','Professional Cloud Architect','cloud','Concevoir des architectures cloud Google Cloud à l’échelle.','Expérience pratique GCP recommandée.',0,4100,'avance',200],
            ['Google Cloud','Professional Cloud DevOps Engineer','dev','Industrialiser les déploiements, la fiabilité et l’observabilité sur GCP.','Expérience cloud et CI/CD.',0,2700,'avance',200],
            ['HashiCorp','Terraform Associate','cloud','Automatiser et décrire l’infrastructure avec Terraform.','Bases cloud et infrastructure as code.',1,7800,'debutant',70],
            ['Microsoft','Power BI Data Analyst Associate','data','Transformer les données et construire des modèles et rapports Power BI.','Bases data et analyse.',1,6800,'intermediaire',165],
            ['Microsoft','Azure Data Fundamentals (DP-900)','data','Comprendre les concepts de données relationnelles, NoSQL et analytiques Azure.','Aucun prérequis technique formel.',0,4300,'debutant',99],
            ['GitLab','GitLab CI/CD Associate','dev','Mettre en place des pipelines CI/CD et collaborer avec GitLab.','Bonnes bases Git et développement.',0,1900,'intermediaire',150],
            ['Red Hat','Red Hat Certified System Administrator (RHCSA)','systeme','Administrer un système Red Hat Linux en environnement professionnel.','Bases Linux et ligne de commande.',1,9200,'intermediaire',500],
            ['Red Hat','Red Hat Certified Engineer (RHCE)','systeme','Automatiser l’administration Linux avec Ansible et des pratiques avancées.','RHCSA ou niveau équivalent.',0,5800,'avance',500],
            ['Cisco','CyberOps Associate','cyber','Développer les bases opérationnelles d’un analyste SOC et de la détection.','Notions réseau et sécurité.',0,2600,'intermediaire',300],
            ['Cisco','CCNP Enterprise','reseau','Approfondir la conception et le troubleshooting de réseaux d’entreprise.','Expérience réseau intermédiaire recommandée.',0,5200,'avance',700],
            ['Cisco','DevNet Associate','dev','Développer des solutions automatisées pour les environnements Cisco.','Bases Python, API et réseau.',0,1900,'intermediaire',300],
            ['CompTIA','Network+','reseau','Valider les fondamentaux réseau, dépannage et infrastructure.','Bases réseau recommandées.',1,7100,'debutant',369],
            ['CompTIA','CySA+','cyber','Analyser les menaces, améliorer la détection et piloter des réponses adaptées.','Expérience sécurité et réseau recommandée.',0,4100,'avance',392],
            ['CompTIA','PenTest+','cyber','Couvrir les méthodes de test d’intrusion, reporting et remédiation.','Bases sécurité et réseau.',0,2500,'avance',392],
            ['Cloud Native Computing Foundation','CKA','cloud','Administrer un cluster Kubernetes et ses workloads.','Bases Linux, conteneurs et Kubernetes.',1,8200,'intermediaire',445],
            ['Cloud Native Computing Foundation','CKAD','dev','Déployer et maintenir des applications cloud native sur Kubernetes.','Expérience conteneurs et Kubernetes.',0,4700,'intermediaire',445],
            ['Cloud Native Computing Foundation','KCNA','cloud','Découvrir l’écosystème cloud native, Kubernetes et les conteneurs.','Aucun prérequis formel.',1,3100,'debutant',250],
            ['OffSec','OSCP','cyber','Certification pratique et exigeante en sécurité offensive et tests d’intrusion.','Solides bases Linux, réseau et sécurité offensive.',1,2100,'avance',1700],
            ['EC-Council','Certified Ethical Hacker (CEH)','cyber','Comprendre les méthodes de reconnaissance, d’attaque et de défense utilisées en sécurité offensive.','Bases réseau et sécurité recommandées.',0,7600,'avance',950],
            ['ISACA','COBIT Foundation','cyber','Comprendre les principes de gouvernance et management de l’IT.','Aucun prérequis technique obligatoire.',0,2100,'debutant',175],
            ['PeopleCert','ITIL 4 Foundation','systeme','Apprendre les concepts de gestion des services IT et de l’amélioration continue.','Aucun prérequis.',1,12600,'debutant',300],
            ['Python Institute','PCAP - Python Programming','dev','Valider les bases solides de programmation Python.','Bases de programmation recommandées.',1,4400,'debutant',250],
            ['Python Institute','PCPP1 - Advanced Python','dev','Approfondir les concepts et outils avancés de Python.','Niveau PCAP ou équivalent.',0,1800,'avance',250],
            ['SAS','SAS Certified Specialist: Base Programming','data','Valider la préparation, la manipulation et l’analyse de données avec SAS.','Bases statistiques et programmation.',0,1700,'intermediaire',180],
            ['Databricks','Generative AI Engineer Associate','ia','Mettre en œuvre des applications GenAI et des pipelines d’IA générative.','Python, SQL et notions ML/LLM.',1,1200,'avance',200],
            ['NVIDIA','NVIDIA-Certified Associate: Generative AI LLMs','ia','Comprendre les fondamentaux techniques des modèles de langage génératifs.','Python et bases ML recommandées.',0,980,'intermediaire',135],
        ];
    }
}
