# Deploy: Traefik + Reverb

## Traefik config

```old
http:
  routers:
    factorioaudit-server-berb8a-router-1:
      rule: Host(`factorio.aidan647.dev`)
      service: factorioaudit-server-berb8a-service-1
      middlewares: []
      entryPoints:
        - web
  services:
    factorioaudit-server-berb8a-service-1:
      loadBalancer:
        servers:
          - url: http://tasks.factorioaudit-server-berb8a:9000
        passHostHeader: true

```

```yaml
http:
  middlewares:
    factorioaudit-ws-headers:
      headers:
        customResponseHeaders:
          Connection: keep-alive
          Access-Control-Allow-Origin: "*"

  routers:
    # HTTP (Laravel)
    factorioaudit-server-berb8a-router-1:
      rule: Host(`factorio.aidan647.dev`)
      service: factorioaudit-server-berb8a-service-1
      entryPoints:
        - web

    # WebSocket (Reverb)
    factorioaudit-reverb-router-1:
      rule: Host(`factorio.aidan647.dev`) && PathPrefix(`/app`)
      service: factorioaudit-reverb-service-1
      middlewares:
        - factorioaudit-ws-headers
      entryPoints:
        - web

    # HTTPS
    factorioaudit-secure-router-1:
      rule: Host(`factorio.aidan647.dev`)
      service: factorioaudit-server-berb8a-service-1
      entryPoints:
        - websecure
      tls:
        certResolver: letsencrypt

    factorioaudit-reverb-secure-router-1:
      rule: Host(`factorio.aidan647.dev`) && PathPrefix(`/app`)
      service: factorioaudit-reverb-service-1
      middlewares:
        - factorioaudit-ws-headers
      entryPoints:
        - websecure
      tls:
        certResolver: letsencrypt

  services:
    factorioaudit-server-berb8a-service-1:
      loadBalancer:
        servers:
          - url: http://tasks.factorioaudit-server-berb8a:9000
        passHostHeader: true

    # Reverb — тот же контейнер app, другой порт
    factorioaudit-reverb-service-1:
      loadBalancer:
        servers:
          - url: http://tasks.factorioaudit-server-berb8a:8080
        passHostHeader: true
```

## Env (.env.local)

```env
# === Broadcast ===
BROADCAST_CONNECTION=reverb

# === Reverb Server (backend) ===
REVERB_APP_ID=675491
REVERB_APP_KEY=vfx0oasrttfdaa2k8chj
REVERB_APP_SECRET=7pztfcv3t9k1yq4vtsze
REVERB_HOST=factorio.aidan647.dev
REVERB_PORT=443
REVERB_SCHEME=https

# === Reverb Client (frontend via Vite) ===
VITE_REVERB_APP_KEY=vfx0oasrttfdaa2k8chj
VITE_REVERB_HOST=factorio.aidan647.dev
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```
