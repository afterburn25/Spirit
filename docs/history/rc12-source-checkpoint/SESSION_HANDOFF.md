# Resume — 1.1.0-rc.12

Repository: afterburn25/Spirit. Branch: work/1.1.0-analytics-and-navigation.
Existing draft PR: 37. rc.10 baseline:
5703ca41a13a6ff01a3b7807287abdeb72c151a5, tree
7fed2c9ae5638bd2af3e971b11b536411b7a3143. Main is not updated.

The exact delivered rc.11 Full ZIP and source patch were recovered. Its ZIP
SHA-256 is 4abbc7d59dc6e1cd003950eecca628d73fa8fb3ecffb1466b73dd1147107168c.
Its production change, class="tabs button-row" on the Music release filters,
remains. The isolated component fixture is excluded from the deployment ZIP
and must never be described as an installed-site test.

Latest request: put the footer at the bottom of pages whose content is shorter
than the viewport. rc.12 gives body a viewport minimum and makes shell/main
flexible; footer height stays content-driven. It also removes the later fixed
116px player-padding override so the existing measured player inset works at
320px. Source CSS and its reproducible minified bundle change; JavaScript and
approved artwork do not. Review docs/TEST_RESULTS.md for actual observations.

The ten deployment folders remain admin, app, assets, config, docs, images,
includes, install, storage and uploads. Do not restore old root handlers, views,
tests, tools or database folders. Preserve all user data and private paths.

Check the current branch head and Actions before continuing; a local test pass
does not establish CI or live-host acceptance. Keep this PR draft until owner
approval. Do not merge, deploy, reinstall, bypass recovery guards or overwrite
any delivered version. Read docs/LAYOUT_TRANSITION.md before updating any
pre-grouped installation. rc.12 introduces no SQL migration or recovery change.
