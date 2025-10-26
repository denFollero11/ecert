Node gateway:
- To run a stub-enabled gateway:
  1. cd node-gateway
  2. npm install
  3. node app.js
- To connect to real Fabric:
  - place a valid connection.json at node-gateway/fabric/connection.json
  - place a wallet folder with identities at node-gateway/fabric/wallet
  - set FABRIC_IDENTITY env var if not 'appUser'