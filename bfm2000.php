<!DOCTYPE>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Baptist Faith and Message 2000</title>
  <style>
html, body {
  margin-top: 3em;
  margin-left: auto;
  margin-right: auto;
  padding: 20px;
  font-family: sans-serif;
  line-height: 1.5;
  max-width: 700px;
  min-width: 300px;
}
details {
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 10px;
  margin: 10px 0;
}

summary {
  cursor: pointer;
  font-weight: bold;
  padding: 5px;
}

summary:hover {
  background-color: #f5f5f5;
}

details[open] summary {
  margin-bottom: 10px;
  border-bottom: 1px solid #ddd;
}
nav {
  background-color: #f9f9f9;
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 20px;
  margin-bottom: 2em;
}

nav a {
  display: block;
  color: #0066cc;
  text-decoration: none;
  padding: 6px 12px;
  margin: 4px;
  border-radius: 3px;
  transition: background-color 0.2s;
}

nav a:hover {
  background-color: #e8e8e8;
  text-decoration: none;
}
  </style>
</head>
<body>

<?php

function kjv_spans_from_scholarly_string($references) {
    $kjv_file = 'kjv.txt';
    
    // Load and parse the KJV file into memory
    $verses = [];
    $lines = file($kjv_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip header lines
        if (strpos($line, "\t") === false) {
            continue;
        }
        
        list($ref, $text) = explode("\t", $line, 2);
        $verses[$ref] = $text;
    }
    
    $results = [];
    
    foreach ($references as $ref) {
        $parsed = parse_reference($ref);
        
        foreach ($parsed as $single_ref) {
            $book = $single_ref['book'];
            $chapter_start = $single_ref['chapter_start'];
            $verse_start = $single_ref['verse_start'];
            $chapter_end = $single_ref['chapter_end'];
            $verse_end = $single_ref['verse_end'];
            
            $text_parts = [];
            
            // Handle single chapter or chapter range
            for ($ch = $chapter_start; $ch <= $chapter_end; $ch++) {
                if ($verse_start === null && $verse_end === null) {
                    // Entire chapter(s)
                    $v = 1;
                    while (isset($verses["$book $ch:$v"])) {
                        $text_parts[] = $verses["$book $ch:$v"];
                        $v++;
                    }
                } else {
                    // Specific verses
                    $start_v = ($ch == $chapter_start) ? $verse_start : 1;
                    $end_v = ($ch == $chapter_end) ? $verse_end : PHP_INT_MAX;
                    
                    $v = $start_v;
                    while ($v <= $end_v && isset($verses["$book $ch:$v"])) {
                        $text_parts[] = $verses["$book $ch:$v"];
                        $v++;
                    }
                }
            }
            
            if (!empty($text_parts)) {
                $results[] = [
                    'original_reference' => $ref,
                    'parsed_reference' => format_parsed_reference($single_ref),
                    'text' => implode(' ', $text_parts)
                ];
            }
        }
    }
    
    echo '<div class="verses">';
    foreach ($results as $result) {
        $ref = $result['original_reference'];
        echo "<details class='verse'><summary>$ref</summary> <p>{$result['text']}</p></details>";
    }
    echo '</div>';
}

function parse_reference($ref) {
    // Normalize "Psalms" to "Psalm"
    $ref = preg_replace('/^Psalms\b/', 'Psalm', $ref);
    
    // Remove "ff." notation (meaning "and following")
    $ref = preg_replace('/ff\..*$/', '', $ref);
    $ref = trim($ref);
    
    $results = [];
    
    // Pattern: "Book Chapter:Verse-Verse" or "Book Chapter" or "Book Chapter-Chapter"
    if (preg_match('/^([A-Za-z0-9\s]+?)\s+(\d+):(\d+)-(\d+)$/', $ref, $matches)) {
        // Book Chapter:StartVerse-EndVerse
        $results[] = [
            'book' => trim($matches[1]),
            'chapter_start' => (int)$matches[2],
            'verse_start' => (int)$matches[3],
            'chapter_end' => (int)$matches[2],
            'verse_end' => (int)$matches[4]
        ];
    } elseif (preg_match('/^([A-Za-z0-9\s]+?)\s+(\d+):(\d+)$/', $ref, $matches)) {
        // Book Chapter:Verse
        $results[] = [
            'book' => trim($matches[1]),
            'chapter_start' => (int)$matches[2],
            'verse_start' => (int)$matches[3],
            'chapter_end' => (int)$matches[2],
            'verse_end' => (int)$matches[3]
        ];
    } elseif (preg_match('/^([A-Za-z0-9\s]+?)\s+(\d+)-(\d+)$/', $ref, $matches)) {
        // Book ChapterStart-ChapterEnd (entire chapters)
        $results[] = [
            'book' => trim($matches[1]),
            'chapter_start' => (int)$matches[2],
            'verse_start' => null,
            'chapter_end' => (int)$matches[3],
            'verse_end' => null
        ];
    } elseif (preg_match('/^([A-Za-z0-9\s]+?)\s+(\d+)$/', $ref, $matches)) {
        // Book Chapter (entire chapter)
        $results[] = [
            'book' => trim($matches[1]),
            'chapter_start' => (int)$matches[2],
            'verse_start' => null,
            'chapter_end' => (int)$matches[2],
            'verse_end' => null
        ];
    }
    
    return $results;
}

function format_parsed_reference($parsed) {
    $book = $parsed['book'];
    $ch_start = $parsed['chapter_start'];
    $v_start = $parsed['verse_start'];
    $ch_end = $parsed['chapter_end'];
    $v_end = $parsed['verse_end'];
    
    if ($v_start === null && $v_end === null) {
        // Entire chapter(s)
        if ($ch_start == $ch_end) {
            return "$book $ch_start";
        } else {
            return "$book $ch_start-$ch_end";
        }
    } else {
        // Specific verses
        if ($ch_start == $ch_end && $v_start == $v_end) {
            return "$book $ch_start:$v_start";
        } elseif ($ch_start == $ch_end) {
            return "$book $ch_start:$v_start-$v_end";
        } else {
            return "$book $ch_start:$v_start-$ch_end:$v_end";
        }
    }
}

// Example usage:
$references = [
    "Exodus 24:4",
    "Deuteronomy 4:1-2",
    "Psalm 1",
    "Revelation 2-3"
];

?>

  <nav>
    <a href="#scriptures">The Scriptures</a>
    <a href="#god">God</a>
    <a href="#man">Man</a></li>
    <a href="#salvation">Salvation</a>
    <a href="#grace">God's Purpose of Grace</a>
    <a href="#church">The Church</a>
    <a href="#baptism">Baptism and the Lord's Supper</a>
    <a href="#lords-day">The Lord's Day</a>
    <a href="#kingdom">The Kingdom</a>
    <a href="#last-things">Last Things</a>
    <a href="#evangelism">Evangelism and Missions</a>
    <a href="#education">Education</a>
    <a href="#stewardship">Stewardship</a>
    <a href="#cooperation">Cooperation</a>
    <a href="#social-order">The Christian and the Social Order</a>
    <a href="#peace-war">Peace and War</a>
    <a href="#religious-liberty">Religious Liberty</a>
    <a href="#family">The Family</a>
  </nav>

  <h1 id="scriptures">The Scriptures</h1>

  <p>The Holy Bible was written by men divinely inspired and is God’s
  revelation of Himself to man. It is a perfect treasure of divine instruction.
  It has God for its author, salvation for its end, and truth, without any
  mixture of error, for its matter. Therefore, all Scripture is totally true
  and trustworthy. It reveals the principles by which God judges us, and
  therefore is, and will remain to the end of the world, the true center of
  Christian union, and the supreme standard by which all human conduct, creeds,
  and religious opinions should be tried. All Scripture is a testimony to
  Christ, who is Himself the focus of divine revelation. </p>

<?php kjv_spans_from_scholarly_string(["Exodus 24:4","Deuteronomy 4:1-2","Deuteronomy 17:19","Joshua 8:34","Psalm 19:7-10","Psalm 119:11","Psalm 119:89","Psalm 119:105","Psalm 119:140","Isaiah 34:16","Isaiah 40:8","Jeremiah 15:16","Jeremiah 36:1-32","Matthew 5:17-18","Matthew 22:29","Luke 21:33","Luke 24:44-46","John 5:39","John 16:13-15","John 17:17","Acts 2:16ff.","Acts 17:11","Romans 15:4","Romans 16:25-26","2 Timothy 3:15-17","Hebrews 1:1-2","Hebrews 4:12","1 Peter 1:25","2 Peter 1:19-21"]); ?>

  <h1 id="god">God</h1>

  <p>There is one and only one living and true God. He is an intelligent,
  spiritual, and personal Being, the Creator, Redeemer, Preserver, and Ruler of
  the universe. God is infinite in holiness and all other perfections. God is
  all powerful and all knowing; and His perfect knowledge extends to all
  things, past, present, and future, including the future decisions of His free
  creatures. To Him we owe the highest love, reverence, and obedience. The
  eternal triune God reveals Himself to us as Father, Son, and Holy Spirit,
  with distinct personal attributes, but without division of nature, essence,
  or being.</p>

  <h2>God the Father</h2>

  <p>God as Father reigns with providential care over His universe, His
  creatures, and the flow of the stream of human history according to the
  purposes of His grace. He is all powerful, all knowing, all loving, and all
  wise. God is Father in truth to those who become children of God through
  faith in Jesus Christ. He is fatherly in His attitude toward all men.</p>

<?php kjv_spans_from_scholarly_string(["Genesis 1:1","Genesis 2:7","Exodus 3:14","Exodus 6:2-3","Exodus 15:11ff.","Exodus 20:1ff.","Leviticus 22:2","Deuteronomy 6:4","Deuteronomy 32:6","1 Chronicles 29:10","Psalm 19:1-3","Isaiah 43:3","Isaiah 3:15","Isaiah 64:8","Jeremiah 10:10","Jeremiah 17:13","Matthew 6:9ff.","Matthew 7:11","Matthew 23:9","Matthew 28:19","Mark 1:9-11","John 4:24","John 5:26","John 14:6-13","John 17:1-8","Acts 1:7","Romans 8:14-15","1 Corinthians 8:6","Galatians 4:6","Ephesians 4:6","Colossians 1:15","1 Timothy 1:17","Hebrews 11:6","Hebrews 12:9","1 Peter 1:17","1 John 5:7"]); ?>

  <h2>God the Son</h2>

  <p>Christ is the eternal Son of God. In His incarnation as Jesus Christ He
  was conceived of the Holy Spirit and born of the virgin Mary. Jesus perfectly
  revealed and did the will of God, taking upon Himself human nature with its
  demands and necessities and identifying Himself completely with mankind yet
  without sin. He honored the divine law by His personal obedience, and in His
  substitutionary death on the cross He made provision for the redemption of
  men from sin. He was raised from the dead with a glorified body and appeared
  to His disciples as the person who was with them before His crucifixion. He
  ascended into heaven and is now exalted at the right hand of God where He is
  the One Mediator, fully God, fully man, in whose Person is effected the
  reconciliation between God and man. He will return in power and glory to
  judge the world and to consummate His redemptive mission. He now dwells in
  all believers as the living and ever present Lord.</p>

<?php kjv_spans_from_scholarly_string(["Genesis 18:1ff.","Psalm 2:7ff.","Psalm 110:1ff.","Isaiah 7:14","Isaiah 53:1-12","Matthew 1:18-23","Matthew 3:17","Matthew 8:29","Matthew 11:27","Matthew 14:33","Matthew 16:16","Matthew 16:27","Matthew 17:5","Matthew 17:27","Matthew 28:1-6","Matthew 28:19","Mark 1:1","Mark 3:11","Luke 1:35","Luke 4:41","Luke 22:70","Luke 24:46","John 1:1-18,29","John 10:30","John 10:38","John 11:25-27","John 12:44-50","John 14:7-11","John 16:15-16,28","John 17:1-5,21-22","John 20:1-20,28","Acts 1:9","Acts 2:22-24","Acts 7:55-56","Acts 9:4-5,20","Romans 1:3-4","Romans 3:23-26","Romans 5:6-21","Romans 8:1-3,34","Romans 10:4","1 Corinthians 1:30","1 Corinthians 2:2","1 Corinthians 8:6","1 Corinthians 15:1-8,24-28","2 Corinthians 5:19-21","2 Corinthians 8:9","Galatians 4:4-5","Ephesians 1:20","Ephesians 3:11","Ephesians 4:7-10","Philippians 2:5-11","Colossians 1:13-22","Colossians 2:9","1 Thessalonians 4:14-18","1 Timothy 2:5-6","1 Timothy 3:16","Titus 2:13-14","Hebrews 1:1-3","Hebrews 4:14-15","Hebrews 7:14-28","Hebrews 9:12-15,24-28","Hebrews 12:2","Hebrews 13:8","1 Peter 2:21-25","1 Peter 3:22","1 John 1:7-9","1 John 3:2","1 John 4:14-15","1 John 5:9","2 John 1:7-9","Revelation 1:13-16","Revelation 5:9-14","Revelation 12:10-11","Revelation 13:8","Revelation 19:16"]); ?>

  <h2>God the Holy Spirit</h2>

  <p>The Holy Spirit is the Spirit of God, fully divine. He inspired holy men
  of old to write the Scriptures. Through illumination He enables men to
  understand truth. He exalts Christ. He convicts men of sin, of righteousness,
  and of judgment. He calls men to the Saviour, and effects regeneration. At
  the moment of regeneration He baptizes every believer into the Body of
  Christ. He cultivates Christian character, comforts believers, and bestows
  the spiritual gifts by which they serve God through His church. He seals the
  believer unto the day of final redemption. His presence in the Christian is
  the guarantee that God will bring the believer into the fullness of the
  stature of Christ. He enlightens and empowers the believer and the church in
  worship, evangelism, and service.</p>

<?php kjv_spans_from_scholarly_string(["Genesis 1:2","Judges 14:6","Job 26:13","Psalm 51:11","Psalm 139:7ff.","Isaiah 61:1-3","Joel 2:28-32","Matthew 1:18","Matthew 3:16","Matthew 4:1","Matthew 12:28-32","Matthew 28:19","Mark 1:10","Mark 1:12","Luke 1:35","Luke 4:1","Luke 4:18-19","Luke 11:13","Luke 12:12","Luke 24:49","John 4:24","John 14:16-17,26","John 15:26","John 16:7-14","Acts 1:8","Acts 2:1-4","Acts 2:38","Acts 4:31","Acts 5:3","Acts 6:3","Acts 7:55","Acts 8:17","Acts 8:39","Acts 10:44","Acts 13:2","Acts 15:28","Acts 16:6","Acts 19:1-6","Romans 8:9-11","Romans 8:14-16","Romans 8:26-27","1 Corinthians 2:10-14","1 Corinthians 3:16","1 Corinthians 12:3-11","1 Corinthians 12:13","Galatians 4:6","Ephesians 1:13-14","Ephesians 4:30","Ephesians 5:18","1 Thessalonians 5:19","1 Timothy 3:16","1 Timothy 4:1","2 Timothy 1:14","2 Timothy 3:16","Hebrews 9:8","Hebrews 9:14","2 Peter 1:21","1 John 4:13","1 John 5:6-7","Revelation 1:10","Revelation 22:17"]); ?>

  <h1 id="man">Man</h1>

  <p>Man is the special creation of God, made in His own image. He created them
  male and female as the crowning work of His creation. The gift of gender is
  thus part of the goodness of God’s creation. In the beginning man was
  innocent of sin and was endowed by his Creator with freedom of choice. By his
  free choice man sinned against God and brought sin into the human race.
  Through the temptation of Satan man transgressed the command of God, and fell
  from his original innocence whereby his posterity inherit a nature and an
  environment inclined toward sin. Therefore, as soon as they are capable of
  moral action, they become transgressors and are under condemnation. Only the
  grace of God can bring man into His holy fellowship and enable man to fulfill
  the creative purpose of God. The sacredness of human personality is evident
  in that God created man in His own image, and in that Christ died for man;
  therefore, every person of every race possesses full dignity and is worthy of
  respect and Christian love.</p>

<?php kjv_spans_from_scholarly_string(["Genesis 1:26-30","Genesis 2:5","Genesis 2:7","Genesis 2:18-22","Genesis 3","Genesis 9:6","Psalm 1","Psalm 8:3-6","Psalm 32:1-5","Psalm 51:5","Isaiah 6:5","Jeremiah 17:5","Matthew 16:26","Acts 17:26-31","Romans 1:19-32","Romans 3:10-18","Romans 3:23","Romans 5:6","Romans 5:12","Romans 5:19","Romans 6:6","Romans 7:14-25","Romans 8:14-18","Romans 8:29","1 Corinthians 1:21-31","1 Corinthians 15:19","1 Corinthians 15:21-22","Ephesians 2:1-22","Colossians 1:21-22","Colossians 3:9-11"]); ?>

  <h1 id="salvation">Salvation</h1>

  <p>Salvation involves the redemption of the whole man, and is offered freely
  to all who accept Jesus Christ as Lord and Saviour, who by His own blood
  obtained eternal redemption for the believer. In its broadest sense salvation
  includes regeneration, justification, sanctification, and glorification.
  There is no salvation apart from personal faith in Jesus Christ as Lord.</p>

  <ol>
    <li>
      <p>Regeneration, or the new birth, is a work of God’s grace
      whereby believers become new creatures in Christ Jesus. It is a change of
      heart wrought by the Holy Spirit through conviction of sin, to which the
      sinner responds in repentance toward God and faith in the Lord Jesus
      Christ. Repentance and faith are inseparable experiences of grace.</p>

      <p>Repentance is a genuine turning from sin toward God. Faith is the
      acceptance of Jesus Christ and commitment of the entire personality to
      Him as Lord and Saviour.</p>
    </li>
    <li><p>Justification is God’s gracious and full acquittal upon principles
      of His righteousness of all sinners who repent and believe in Christ.
      Justification brings the believer unto a relationship of peace and favor
    with God.<p></li>
    <li><p>Sanctification is the experience, beginning in regeneration, by
      which the believer is set apart to God’s purposes, and is enabled to
      progress toward moral and spiritual maturity through the presence and
      power of the Holy Spirit dwelling in him. Growth in grace should continue
      throughout the regenerate person’s life.<p></li>
    <li><p>Glorification is the culmination of salvation and is the final
      blessed and abiding state of the redeemed.</p></li>
  </ol>

<?php kjv_spans_from_scholarly_string(["Genesis 3:15","Exodus 3:14-17","Exodus 6:2-8","Matthew 1:21","Matthew 4:17","Matthew 16:21-26","Matthew 27:22-28:6","Luke 1:68-69","Luke 2:28-32","John 1:11-14,29","John 3:3-21,36","John 5:24","John 10:9,28-29","John 15:1-16","John 17:17","Acts 2:21","Acts 4:12","Acts 15:11","Acts 16:30-31","Acts 17:30-31","Acts 20:32","Romans 1:16-18","Romans 2:4","Romans 3:23-25","Romans 4:3ff.","Romans 5:8-10","Romans 6:1-23","Romans 8:1-18,29-39","Romans 10:9-10,13","Romans 13:11-14","1 Corinthians 1:18,30","1 Corinthians 6:19-20","1 Corinthians 15:10","2 Corinthians 5:17-20","Galatians 2:20","Galatians 3:13","Galatians 5:22-25","Galatians 6:15","Ephesians 1:7","Ephesians 2:8-22","Ephesians 4:11-16","Philippians 2:12-13","Colossians 1:9-22","Colossians 3:1ff.","1 Thessalonians 5:23-24","2 Timothy 1:12","Titus 2:11-14","Hebrews 2:1-3","Hebrews 5:8-9","Hebrews 9:24-28","Hebrews 11:1-12:8,14","James 2:14-26","1 Peter 1:2-23","1 John 1:6-2:11","Revelation 3:20","Revelation 21:1-22:5"]); ?>

  <h1 id="grace">God's Purpose of Grace</h1>

  <p>Election is the gracious purpose of God, according to which He
  regenerates, justifies, sanctifies, and glorifies sinners. It is consistent
  with the free agency of man, and comprehends all the means in connection with
  the end. It is the glorious display of God’s sovereign goodness, and is
  infinitely wise, holy, and unchangeable. It excludes boasting and promotes
  humility.</p>

  <p>All true believers endure to the end. Those whom God has accepted in
  Christ, and sanctified by His Spirit, will never fall away from the state of
  grace, but shall persevere to the end. Believers may fall into sin through
  neglect and temptation, whereby they grieve the Spirit, impair their graces
  and comforts, and bring reproach on the cause of Christ and temporal
  judgments on themselves; yet they shall be kept by the power of God through
  faith unto salvation.</p>

<?php kjv_spans_from_scholarly_string(["Genesis 12:1-3","Exodus 19:5-8","1 Samuel 8:4-7,19-22","Isaiah 5:1-7","Jeremiah 31:31ff.","Matthew 16:18-19","Matthew 21:28-45","Matthew 24:22,31","Matthew 25:34","Luke 1:68-79","Luke 2:29-32","Luke 19:41-44","Luke 24:44-48","John 1:12-14","John 3:16","John 5:24","John 6:44-45,65","John 10:27-29","John 15:16","John 17:6,12,17-18","Acts 20:32","Romans 5:9-10","Romans 8:28-39","Romans 10:12-15","Romans 11:5-7,26-36","1 Corinthians 1:1-2","1 Corinthians 15:24-28","Ephesians 1:4-23","Ephesians 2:1-10","Ephesians 3:1-11","Colossians 1:12-14","2 Thessalonians 2:13-14","2 Timothy 1:12","2 Timothy 2:10,19","Hebrews 11:39–12:2","James 1:12","1 Peter 1:2-5,13","1 Peter 2:4-10","1 John 1:7-9","1 John 2:19","1 John 3:2"]); ?>

  <h1 id="church">The Church</h1>

  <p>A New Testament church of the Lord Jesus Christ is an autonomous local
  congregation of baptized believers, associated by covenant in the faith and
  fellowship of the gospel; observing the two ordinances of Christ, governed by
  His laws, exercising the gifts, rights, and privileges invested in them by
  His Word, and seeking to extend the gospel to the ends of the earth. Each
  congregation operates under the Lordship of Christ through democratic
  processes. In such a congregation each member is responsible and accountable
  to Christ as Lord. Its two scriptural offices are that of
  pastor/elder/overseer and deacon. While both men and women are gifted for
  service in the church, the office of pastor/elder/overseer is limited to men
  as qualified by Scripture.</p>

  <p>The New Testament speaks also of the church as the Body of Christ which
  includes all of the redeemed of all the ages, believers from every tribe, and
  tongue, and people, and nation.</p>

<?php kjv_spans_from_scholarly_string(["Matthew 16:15-19","Matthew 18:15-20","Acts 2:41-42,47","Acts 5:11-14","Acts 6:3-6","Acts 13:1-3","Acts 14:23,27","Acts 15:1-30","Acts 16:5","Acts 20:28","Romans 1:7","1 Corinthians 1:2","1 Corinthians 3:16","1 Corinthians 5:4-5","1 Corinthians 7:17","1 Corinthians 9:13-14","1 Corinthians 12","Ephesians 1:22-23","Ephesians 2:19-22","Ephesians 3:8-11,21","Ephesians 5:22-32","Philippians 1:1","Colossians 1:18","1 Timothy 2:9-14","1 Timothy 3:1-15","1 Timothy 4:14","Hebrews 11:39-40","1 Peter 5:1-4","Revelation 2-3","Revelation 21:2-3"]); ?>

  <p><b>**Note: This article was amended June 14, 2023, by action of the 2023 Southern Baptist Convention**</b></p>

  <h1 id="baptism">Baptism and the Lord's Supper</h1>

  <p>Christian baptism is the immersion of a believer in water in the name of
  the Father, the Son, and the Holy Spirit. It is an act of obedience
  symbolizing the believer’s faith in a crucified, buried, and risen Saviour,
  the believer’s death to sin, the burial of the old life, and the resurrection
  to walk in newness of life in Christ Jesus. It is a testimony to his faith in
  the final resurrection of the dead. Being a church ordinance, it is
  prerequisite to the privileges of church membership and to the Lord’s
  Supper.</p>

  <p>The Lord’s Supper is a symbolic act of obedience whereby members of the
  church, through partaking of the bread and the fruit of the vine, memorialize
  the death of the Redeemer and anticipate His second coming.</p>

<?php kjv_spans_from_scholarly_string(["Matthew 3:13-17","Matthew 26:26-30","Matthew 28:19-20","Mark 1:9-11","Mark 14:22-26","Luke 3:21-22","Luke 22:19-20","John 3:23","Acts 2:41-42","Acts 8:35-39","Acts 16:30-33","Acts 20:7","Romans 6:3-5","1 Corinthians 10:16,21","1 Corinthians 11:23-29","Colossians 2:12"]); ?>

  <h1 id="lords-day">The Lord's Day</h1>

  <p>The first day of the week is the Lord’s Day. It is a Christian institution
  for regular observance. It commemorates the resurrection of Christ from the
  dead and should include exercises of worship and spiritual devotion, both
  public and private. Activities on the Lord’s Day should be commensurate with
  the Christian’s conscience under the Lordship of Jesus Christ.</p>

<?php kjv_spans_from_scholarly_string(["Exodus 20:8-11","Matthew 12:1-12","Matthew 28:1ff.","Mark 2:27-28","Mark 16:1-7","Luke 24:1-3,33-36","John 4:21-24","John 20:1,19-28","Acts 20:7","Romans 14:5-10","1 Corinthians 16:1-2","Colossians 2:16","Colossians 3:16","Revelation 1:10"]); ?>

  <h1 id="kingdom">The Kingdom</h1>

  <p>The Kingdom of God includes both His general sovereignty over the universe
  and His particular kingship over men who willfully acknowledge Him as King.
  Particularly the Kingdom is the realm of salvation into which men enter by
  trustful, childlike commitment to Jesus Christ. Christians ought to pray and
  to labor that the Kingdom may come and God’s will be done on earth. The full
  consummation of the Kingdom awaits the return of Jesus Christ and the end of
  this age.</p>

<?php kjv_spans_from_scholarly_string(["Genesis 1:1","Isaiah 9:6-7","Jeremiah 23:5-6","Matthew 3:2","Matthew 4:8-10,23","Matthew 12:25-28","Matthew 13:1-52","Matthew 25:31-46","Matthew 26:29","Mark 1:14-15","Mark 9:1","Luke 4:43","Luke 8:1","Luke 9:2","Luke 12:31-32","Luke 23:42","John 3:3","John 18:36","Acts 1:6-7","Acts 17:22-31","Romans 5:17","Romans 8:19","1 Corinthians 15:24-28","Colossians 1:13","Hebrews 11:10,16","Hebrews 12:28","1 Peter 2:4-10","1 Peter 4:13","Revelation 1:6,9","Revelation 5:10","Revelation 11:15","Revelation 21-22"]); ?>

  <h1 id="last-things">Last Things</h1>

  <p>God, in His own time and in His own way, will bring the world to its
  appropriate end. According to His promise, Jesus Christ will return
  personally and visibly in glory to the earth; the dead will be raised; and
  Christ will judge all men in righteousness. The unrighteous will be consigned
  to Hell, the place of everlasting punishment. The righteous in their
  resurrected and glorified bodies will receive their reward and will dwell
  forever in Heaven with the Lord.</p>

<?php kjv_spans_from_scholarly_string(["Isaiah 2:4","Isaiah 11:9","Matthew 16:27","Matthew 18:8-9","Matthew 19:28","Matthew 24:27,30,36,44","Matthew 25:31-46","Matthew 26:64","Mark 8:38","Mark 9:43-48","Luke 12:40,48","Luke 16:19-26","Luke 17:22-37","Luke 21:27-28","John 14:1-3","Acts 1:11","Acts 17:31","Romans 14:10","1 Corinthians 4:5","1 Corinthians 15:24-28,35-58","2 Corinthians 5:10","Philippians 3:20-21","Colossians 1:5","Colossians 3:4","1 Thessalonians 4:14-18","1 Thessalonians 5:1ff.","2 Thessalonians 1:7ff.,2","1 Timothy 6:14","2 Timothy 4:1,8","Titus 2:13","Hebrews 9:27-28","James 5:8","2 Peter 3:7ff.","1 John 2:28","1 John 3:2","Jude 14","Revelation 1:18","Revelation 3:11","Revelation 20:1-22:13"]); ?>

  <h1 id="evangelism">Evangelism and Missions</h1>

  <p>It is the duty and privilege of every follower of Christ and of every
  church of the Lord Jesus Christ to endeavor to make disciples of all nations.
  The new birth of man’s spirit by God’s Holy Spirit means the birth of love
  for others. Missionary effort on the part of all rests thus upon a spiritual
  necessity of the regenerate life, and is expressly and repeatedly commanded
  in the teachings of Christ. The Lord Jesus Christ has commanded the preaching
  of the gospel to all nations. It is the duty of every child of God to seek
  constantly to win the lost to Christ by verbal witness undergirded by a
  Christian lifestyle, and by other methods in harmony with the gospel of
  Christ.</p>

<?php kjv_spans_from_scholarly_string(["Genesis 12:1-3","Exodus 19:5-6","Isaiah 6:1-8","Matthew 9:37-38","Matthew 10:5-15","Matthew 13:18-30,37-43","Matthew 16:19","Matthew 22:9-10","Matthew 24:14","Matthew 28:18-20","Luke 10:1-18","Luke 24:46-53","John 14:11-12","John 15:7-8,16","John 17:15","John 20:21","Acts 1:8","Acts 2","Acts 8:26-40","Acts 10:42-48","Acts 13:2-3","Romans 10:13-15","Ephesians 3:1-11","1 Thessalonians 1:8","2 Timothy 4:5","Hebrews 2:1-3","Hebrews 11:39-12:2","1 Peter 2:4-10","Revelation 22:17"]); ?>

  <h1 id="education">Education</h1>

  <p>Christianity is the faith of enlightenment and intelligence. In Jesus
  Christ abide all the treasures of wisdom and knowledge. All sound learning
  is, therefore, a part of our Christian heritage. The new birth opens all
  human faculties and creates a thirst for knowledge. Moreover, the cause of
  education in the Kingdom of Christ is co-ordinate with the causes of missions
  and general benevolence, and should receive along with these the liberal
  support of the churches. An adequate system of Christian education is
  necessary to a complete spiritual program for Christ’s people.</p>

  <p>In Christian education there should be a proper balance between academic
  freedom and academic responsibility. Freedom in any orderly relationship of
  human life is always limited and never absolute. The freedom of a teacher in
  a Christian school, college, or seminary is limited by the pre-eminence of
  Jesus Christ, by the authoritative nature of the Scriptures, and by the
  distinct purpose for which the school exists.</p>

<?php kjv_spans_from_scholarly_string(["Deuteronomy 4:1,5,9,14","Deuteronomy 6:1-10","Deuteronomy 31:12-13","Nehemiah 8:1-8","Job 28:28","Psalm 19:7ff.","Psalm 119:11","Proverbs 3:13ff.","Proverbs 4:1-10","Proverbs 8:1-7,11","Proverbs 15:14","Ecclesiastes 7:19","Matthew 5:2","Matthew 7:24ff.","Matthew 28:19-20","Luke 2:40","1 Corinthians 1:18-31","Ephesians 4:11-16","Philippians 4:8","Colossians 2:3,8-9","1 Timothy 1:3-7","2 Timothy 2:15","2 Timothy 3:14-17","Hebrews 5:12-6:3","James 1:5","James 3:17"]); ?>

  <h1 id="stewardship">Stewardship</h1>

  <p>God is the source of all blessings, temporal and spiritual; all that we
  have and are we owe to Him. Christians have a spiritual debtorship to the
  whole world, a holy trusteeship in the gospel, and a binding stewardship in
  their possessions. They are therefore under obligation to serve Him with
  their time, talents, and material possessions; and should recognize all these
  as entrusted to them to use for the glory of God and for helping others.
  According to the Scriptures, Christians should contribute of their means
  cheerfully, regularly, systematically, proportionately, and liberally for the
  advancement of the Redeemer’s cause on earth.</p>

<?php kjv_spans_from_scholarly_string(["Genesis 14:20","Leviticus 27:30-32","Deuteronomy 8:18","Malachi 3:8-12","Matthew 6:1-4,19-21","Matthew 19:21","Matthew 23:23","Matthew 25:14-29","Luke 12:16-21,42","Luke 16:1-13","Acts 2:44-47","Acts 5:1-11","Acts 17:24-25","Acts 20:35","Romans 6:6-22","Romans 12:1-2","1 Corinthians 4:1-2","1 Corinthians 6:19-20","1 Corinthians 12","1 Corinthians 16:1-4","2 Corinthians 8-9","2 Corinthians 12:15","Philippians 4:10-19","1 Peter 1:18-19"]); ?>

  <h1 id="cooperation">Cooperation</h1>

  <p>Christ’s people should, as occasion requires, organize such associations
  and conventions as may best secure cooperation for the great objects of the
  Kingdom of God. Such organizations have no authority over one another or over
  the churches. They are voluntary and advisory bodies designed to elicit,
  combine, and direct the energies of our people in the most effective manner.
  Members of New Testament churches should cooperate with one another in
  carrying forward the missionary, educational, and benevolent ministries for
  the extension of Christ’s Kingdom. Christian unity in the New Testament sense
  is spiritual harmony and voluntary cooperation for common ends by various
  groups of Christ’s people. Cooperation is desirable between the various
  Christian denominations, when the end to be attained is itself justified, and
  when such cooperation involves no violation of conscience or compromise of
  loyalty to Christ and His Word as revealed in the New Testament.</p>

<?php kjv_spans_from_scholarly_string(["Exodus 17:12","Exodus 18:17ff.","Judges 7:21","Ezra 1:3-4","Ezra 2:68-69","Ezra 5:14-15","Nehemiah 4","Nehemiah 8:1-5","Matthew 10:5-15","Matthew 20:1-16","Matthew 22:1-10","Matthew 28:19-20","Mark 2:3","Luke 10:1ff.","Acts 1:13-14","Acts 2:1ff.","Acts 4:31-37","Acts 13:2-3","Acts 15:1-35","1 Corinthians 1:10-17","1 Corinthians 3:5-15","1 Corinthians 12","2 Corinthians 8-9","Galatians 1:6-10","Ephesians 4:1-16","Philippians 1:15-18"]); ?>

  <h1 id="social-order">The Christian and the Social Order</h1>

  <p>All Christians are under obligation to seek to make the will of Christ
  supreme in our own lives and in human society. Means and methods used for the
  improvement of society and the establishment of righteousness among men can
  be truly and permanently helpful only when they are rooted in the
  regeneration of the individual by the saving grace of God in Jesus Christ. In
  the spirit of Christ, Christians should oppose racism, every form of greed,
  selfishness, and vice, and all forms of sexual immorality, including
  adultery, homosexuality, and pornography. We should work to provide for the
  orphaned, the needy, the abused, the aged, the helpless, and the sick. We
  should speak on behalf of the unborn and contend for the sanctity of all
  human life from conception to natural death. Every Christian should seek to
  bring industry, government, and society as a whole under the sway of the
  principles of righteousness, truth, and brotherly love. In order to promote
  these ends Christians should be ready to work with all men of good will in
  any good cause, always being careful to act in the spirit of love without
  compromising their loyalty to Christ and His truth.</p>

<?php kjv_spans_from_scholarly_string(["Exodus 20:3-17","Leviticus 6:2-5","Deuteronomy 10:12","Deuteronomy 27:17","Psalm 101:5","Micah 6:8","Zechariah 8:16","Matthew 5:13-16,43-48","Matthew 22:36-40","Matthew 25:35","Mark 1:29-34","Mark 2:3ff.","Mark 10:21","Luke 4:18-21","Luke 10:27-37","Luke 20:25","John 15:12","John 17:15","Romans 12-14","1 Corinthians 5:9-10","1 Corinthians 6:1-7","1 Corinthians 7:20-24","1 Corinthians 10:23-11:1","Galatians 3:26-28","Ephesians 6:5-9","Colossians 3:12-17","1 Thessalonians 3:12","Philemon","James 1:27","James 2:8"]); ?>

  <h1 id="peace-war">Peace and War</h1>

  <p>It is the duty of Christians to seek peace with all men on principles of
  righteousness. In accordance with the spirit and teachings of Christ they
  should do all in their power to put an end to war.</p>

  <p>The true remedy for the war spirit is the gospel of our Lord. The supreme
  need of the world is the acceptance of His teachings in all the affairs of
  men and nations, and the practical application of His law of love. Christian
  people throughout the world should pray for the reign of the Prince of
  Peace.</p>

<?php kjv_spans_from_scholarly_string(["Isaiah 2:4","Matthew 5:9,38-48","Matthew 6:33","Matthew 26:52","Luke 22:36,38","Romans 12:18-19","Romans 13:1-7","Romans 14:19","Hebrews 12:14","James 4:1-2"]); ?>

  <h1 id="religious-liberty">Religious Liberty</h1>

  <p>God alone is Lord of the conscience, and He has left it free from the
  doctrines and commandments of men which are contrary to His Word or not
  contained in it. Church and state should be separate. The state owes to every
  church protection and full freedom in the pursuit of its spiritual ends. In
  providing for such freedom no ecclesiastical group or denomination should be
  favored by the state more than others. Civil government being ordained of
  God, it is the duty of Christians to render loyal obedience thereto in all
  things not contrary to the revealed will of God. The church should not resort
  to the civil power to carry on its work. The gospel of Christ contemplates
  spiritual means alone for the pursuit of its ends. The state has no right to
  impose penalties for religious opinions of any kind. The state has no right
  to impose taxes for the support of any form of religion. A free church in a
  free state is the Christian ideal, and this implies the right of free and
  unhindered access to God on the part of all men, and the right to form and
  propagate opinions in the sphere of religion without interference by the
  civil power.</p>

<?php kjv_spans_from_scholarly_string(["Genesis 1:27","Genesis 2:7","Matthew 6:6-7,24","Matthew 16:26","Matthew 22:21","John 8:36","Acts 4:19-20","Romans 6:1-2","Romans 13:1-7","Galatians 5:1,13","Philippians 3:20","1 Timothy 2:1-2","James 4:12","1 Peter 2:12-17","1 Peter 3:11-17","1 Peter 4:12-19"]); ?>

  <h1 id="family">The Family</h1>

  <p>God has ordained the family as the foundational institution of human
  society. It is composed of persons related to one another by marriage, blood,
  or adoption.</p>

  <p>Marriage is the uniting of one man and one woman in covenant commitment
  for a lifetime. It is God’s unique gift to reveal the union between Christ
  and His church and to provide for the man and the woman in marriage the
  framework for intimate companionship, the channel of sexual expression
  according to biblical standards, and the means for procreation of the human
  race.</p>

  <p>The husband and wife are of equal worth before God, since both are created
  in God’s image. The marriage relationship models the way God relates to His
  people. A husband is to love his wife as Christ loved the church. He has the
  God-given responsibility to provide for, to protect, and to lead his family.
  A wife is to submit herself graciously to the servant leadership of her
  husband even as the church willingly submits to the headship of Christ. She,
  being in the image of God as is her husband and thus equal to him, has the
  God-given responsibility to respect her husband and to serve as his helper in
  managing the household and nurturing the next generation.</p>

  <p>Children, from the moment of conception, are a blessing and heritage from
  the Lord. Parents are to demonstrate to their children God’s pattern for
  marriage. Parents are to teach their children spiritual and moral values and
  to lead them, through consistent lifestyle example and loving discipline, to
  make choices based on biblical truth. Children are to honor and obey their
  parents.</p>

<?php kjv_spans_from_scholarly_string(["Genesis 1:26-28","Genesis 2:15-25","Genesis 3:1-20","Exodus 20:12","Deuteronomy 6:4-9","Joshua 24:15","1 Samuel 1:26-28","Psalm 51:5","Psalm 78:1-8","Psalm 127","Psalm 128","Psalm 139:13-16","Proverbs 1:8","Proverbs 5:15-20","Proverbs 6:20-22","Proverbs 12:4","Proverbs 13:24","Proverbs 14:1","Proverbs 17:6","Proverbs 18:22","Proverbs 22:6","Proverbs 23:13-14","Proverbs 24:3","Proverbs 29:15,17","Proverbs 31:10-31","Ecclesiastes 4:9-12","Ecclesiastes 9:9","Malachi 2:14-16","Matthew 5:31-32","Matthew 18:2-5","Matthew 19:3-9","Mark 10:6-12","Romans 1:18-32","1 Corinthians 7:1-16","Ephesians 5:21-33","Ephesians 6:1-4","Colossians 3:18-21","1 Timothy 5:8,14","2 Timothy 1:3-5","Titus 2:3-5","Hebrews 13:4","1 Peter 3:1-7"]); ?>

</body>
</html>
