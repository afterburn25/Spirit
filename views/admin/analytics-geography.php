<div class="page-heading"><div><p class="eyebrow">BACKSTAGE / ANALYTICS</p><h1>Understand your<br><em>audience geography.</em></h1><p class="lead">Country estimates are included. Add an optional City database for region and city estimates where the source has them.</p></div><a class="button secondary" href="<?=e(app_url('admin/analytics/'))?>">Back to analytics</a></div>
<?php Spirit\View::notice($error); ?>
<section class="panel">
 <h2>Current geography data</h2>
 <div class="table-scroll"><table class="content-table"><caption class="sr-only">Offline country and City database availability</caption><thead><tr><th scope="col">Coverage</th><th scope="col">Status</th><th scope="col">Source</th><th scope="col">Source date (UTC)</th></tr></thead><tbody>
 <tr><th scope="row">Country</th><td><?=e($geography['country_available'] ? 'Ready' : 'Unavailable — ' . str_replace('_', ' ', $geography['country_status']))?></td><td>DB-IP Lite Country</td><td><?=e($geography['country_version'] ?: 'Unavailable')?></td></tr>
 <tr><th scope="row">Region / city</th><td><?=e($geography['city_available'] ? 'Ready where available' : ($geography['city_status'] === 'missing' ? 'Not installed' : 'Unavailable — ' . str_replace('_', ' ', $geography['city_status'])))?></td><td><?=e($geography['city_source'] ?: 'Owner-provided City MMDB')?></td><td><?=e($geography['city_version'] ?: 'Unavailable')?></td></tr>
 </tbody></table></div>
 <p class="small muted">Dates come from each database's source metadata. IP geography is an approximate network location. Missing region or city names stay unavailable. Database changes apply to new lookups; saved visit history keeps its original location labels.</p>
 <p class="small muted">Lookups run locally on your hosting account. This feature does not send visitor IP addresses to a geolocation provider or store those IP addresses in analytics.</p>
 <p class="small"><a class="text-link" href="https://db-ip.com" target="_blank" rel="noopener noreferrer">IP Geolocation by DB-IP</a> · Lite data is licensed under <a class="text-link" href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener noreferrer">CC BY 4.0</a>.</p>
</section>
<section class="panel">
 <h2><?=$geography['city_available'] ? 'Replace the City database' : 'Add a City database'?></h2>
 <ol>
  <li>Download the <strong>MMDB</strong> version from the <a class="text-link" href="https://db-ip.com/db/download/ip-to-city-lite" target="_blank" rel="noopener noreferrer">official DB-IP Lite City page ↗</a>. The free data is updated monthly and carries the attribution shown here. You can also use a compatible City MMDB that you have the right to use; keep any additional attribution its license requires.</li>
  <li>Select the downloaded <strong>.mmdb.gz</strong> file below. An extracted <strong>.mmdb</strong> also works. CSV, ZIP and other archive formats are not accepted.</li>
  <li>Upload the file. The site checks and stores it privately before new visits can use it. A failed validation keeps the previous database in place.</li>
 </ol>
 <p class="small muted"><strong>Application limit:</strong> 256 MiB for both the uploaded file and the expanded database. Processing is limited to 20 seconds. Your hosting file and whole-request limits can be lower; a whole request also includes the form and upload framing.</p>
 <p class="small muted"><?=e(Spirit\HostingLimits::uploadHint())?></p>
 <?php if (!$gzipAvailable): ?><p class="notice error">PHP zlib extraction is unavailable on this host. Extract the .mmdb on your computer before uploading, or enable PHP zlib in cPanel.</p><?php endif; ?>
 <?php if (!$limits['file_uploads']): ?><p class="notice error">PHP file uploads are disabled. Enable them in cPanel or ask A2 to enable them before using this form.</p><?php endif; ?>
 <form method="post" enctype="multipart/form-data" class="form-stack">
  <?=csrf_field()?><input type="hidden" name="action" value="upload"><input type="hidden" name="MAX_FILE_SIZE" value="268435456">
  <label class="field-stack" for="city-database">City database file<input id="city-database" type="file" name="city_database" accept=".mmdb,.mmdb.gz,application/gzip,application/octet-stream" required<?=!$limits['file_uploads'] ? ' disabled' : ''?>></label>
  <p class="small muted">If the download exceeds the displayed PHP limit, adjust <code>upload_max_filesize</code> and <code>post_max_size</code> in cPanel's MultiPHP INI Editor, or ask A2 for help. Use a whole-request limit larger than the file. No SSH or API key is needed.</p>
  <button class="button" type="submit"<?=!$limits['file_uploads'] ? ' disabled' : ''?>><?=$geography['city_available'] ? 'Validate and replace City database' : 'Validate and install City database'?></button>
 </form>
</section>
<?php if ($geography['city_status'] !== 'missing'): ?>
<section class="panel"><h2>Remove the optional City database</h2><p>New visits will use the included country database. Existing analytics history stays available with its original country, region and city labels.</p><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="remove"><button class="button secondary" type="submit">Remove City database</button></form></section>
<?php endif; ?>
