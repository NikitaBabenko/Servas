# Guided preparation contract

**NOT_READY: published experimental manual profile; static review only. No badge,
build, application, test-owner session, Free fit or persistence acceptance is claimed.**

This branch adapts the reviewed private profile at
`a3cd21932f5f8699b664b7453d5f3f9ea079ddc4` for the ordinary dashboard's root
Dockerfile setting. That original branch and its required-disk Blueprint remain
the reference. The original upstream Dockerfile is preserved without edits at
`vibenest/upstream/Dockerfile`. It can be copied back to the repository root when
building the original upstream profile. All other upstream examples and source
remain in their original locations. No files are removed to change security scope.

The root COPY-only wrapper is identical to `vibenest/Dockerfile`. The required
mount is **`/app/database/sqlite`**. The initializer rejects an absent mount before
creating a database or starting HTTP. The root `render.yaml` still declares its
required disk and still receives `render_persistent_disk_unsupported` on the
reviewed platform. It is not an unattended deployment contract.

## Reviewed platform behavior

Static source revision: `a54407722891026bada4631360c5b7f5ee5ff193`.
These findings need fresh production confirmation before action.

| UI step | Static effect and boundary |
| --- | --- |
| Dashboard: create project with repository empty | Creates project metadata and a stub wiki page. FirstVersionQueue is conditional on a nonempty repository. No hardware or deployment is requested in this blank path. |
| Storage: save required volume path | Saves the path, then **always calls the deployment dispatcher**. With the repository still null, the orchestrator returns before source work, hardware, provisioning and attempt creation. This is not a dispatch-free button. |
| Environment: add/save variables | Persists encrypted values and marks changes unsynced. **Apply to Deployment** is separate and syncs/restarts; it must not be used during preparation. |
| Deployment: Save Configuration | Proves source access and saves settings; the handler does not dispatch. It has build pack, base directory and port fields, but no Dockerfile-location field. Therefore this branch provides a root Dockerfile. |
| Deployment: Deploy | Enters the normal gated orchestrator. Later provisioning verifies/creates the required volume before deployment dispatch. This is a distinct execution boundary. |

For an unbound project, repository authorization first proves anonymous public
read access. Only after that fails does it discover GitHub App access. Settings
persists the proven credential kind. A persisted App grant remains authoritative;
it must never be cleared to force public access. Ordinary `GitHubAppDirect` plus
persistent storage is unsupported. Stop if the resulting source is not naturally
`PublicAnonymous`; do not edit source mode or GitHub permissions.

## Proposed manual sequence, subject to coordinator authorization

1. Confirm the designated test owner from the current session, deduplicate its
   projects, and record the intended fork, branch and exact revision. Do not infer
   identity from another pilot's session or receipt. Confirm current policy and
   project restrictions; unknown or blocked state does not permit execution.
2. Create one blank-repository metadata draft only. Verify exact owner, repository
   null, no grant, no FirstVersion job, hardware, provider UUID, provisioning
   operation or deployment attempt. Do not paste the repository in this form.
3. While the repository remains null, save `/app/database/sqlite` in Storage.
   Expect the dispatcher rejection for missing repository; verify the saved path,
   no volume UUID/binding and the same zero-runtime state. A toast alone is not
   sufficient evidence. This step requires separate authorization because the
   button invokes the dispatcher.
4. Add the five owner values from [VIBENEST.md](VIBENEST.md) using native sensitive
   ENV storage. Keep values out of evidence. Verify key names and sensitive flags.
   Do not use **Apply to Deployment**. Preserve the stable APP_KEY for this volume.
5. Save the published public fork URL and reviewed branch in Deployment, with
   build pack `dockerfile`, base directory `/`, port `3000`. Verify persisted
   `PublicAnonymous`, no App grant, root Dockerfile selection, exact repository and
   branch, required mount path and all ENV keys. Verify again that no runtime,
   attempt or hardware has appeared. Do not click Deploy yet.
6. Only after an explicit Free-slot assignment and fresh safety/eligibility
   checks, launch through the ordinary gated path. Preserve the actual admission
   result; a prior read-only inventory snapshot is not admission. Verify exact
   serving revision, volume identity/mount, HTTPS login and the acceptance matrix
   in VIBENEST.md, including data survival after a normal replacement.

None of steps 2–6 has been performed for this candidate. Storage, ENV changes,
real-repository saves and launch continuation are outside the present static task.
Capacity failure, a hold, App source selection or an unverified mount stops that
attempt; do not try another provider path, clone or elevated tier as Free.

## Badge and authentication are a separate, unaccepted path

`/deploy` redirects to `/start` preserving repo and branch. `/start/continue`
stores a launch handoff and routes through authentication. Single-service preview
goes to `/dashboard/launch`, which calls QuickDeploy after render and can dispatch
automatically. It is not a harmless login check. Its reuse lookup matches owner
and repository, not this guided preparation checklist.

The current tree contains nested Dockerfiles at `/vibenest` and
`/vibenest/upstream`. The exact preview heuristic counts two service candidates
and therefore routes this branch to `/dashboard/new-group`. There, the required
disk in the Blueprint remains unsupported. Do not remove source, hide the disk,
forge a template/mono query, or assert reuse of the manually prepared project.
No deploy badge is published. A future preview/auth test and the manual guided
deployment need separate evidence; success of one does not establish the other.
