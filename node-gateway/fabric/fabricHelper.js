const fs = require('fs');
const path = require('path');
const { Gateway, Wallets } = require('fabric-network');
const { v4: uuidv4 } = require('uuid');

class FabricHelper {
  constructor(options = {}) {
    this.connectionProfilePath = options.connectionProfile || path.resolve(__dirname, 'connection.json');
    this.walletPath = options.walletPath || path.resolve(__dirname, 'wallet');
    this.identity = options.identity || 'appUser';
    this.channelName = options.channelName || 'mychannel';
    this.chaincodeName = options.chaincodeName || 'certificate-contract';
    this.stubIfNoFabric = options.stubIfNoFabric !== undefined ? options.stubIfNoFabric : true;

    this.gateway = null;
    this.contract = null;
    this.initialized = false;
    this.stubMode = false;
  }

  async init() {
    if (this.initialized) return;
    const ccpExists = fs.existsSync(this.connectionProfilePath);
    const walletExists = fs.existsSync(this.walletPath);

    if (!ccpExists || !walletExists) {
      if (this.stubIfNoFabric) {
        console.warn('Fabric connection profile or wallet not found — running in STUB mode.');
        this.stubMode = true;
        this.initialized = true;
        return;
      } else {
        throw new Error('Fabric connection profile or wallet not found and stubIfNoFabric=false');
      }
    }

    try {
      const ccpJSON = fs.readFileSync(this.connectionProfilePath, 'utf8');
      const ccp = JSON.parse(ccpJSON);

      const wallet = await Wallets.newFileSystemWallet(this.walletPath);
      const identity = await wallet.get(this.identity);
      if (!identity) {
        console.warn(`Identity ${this.identity} not found in wallet — running in STUB mode.`);
        this.stubMode = true;
        this.initialized = true;
        return;
      }

      this.gateway = new Gateway();
      await this.gateway.connect(ccp, {
        wallet,
        identity: this.identity,
        discovery: { enabled: true, asLocalhost: true }
      });

      const network = await this.gateway.getNetwork(this.channelName);
      this.contract = network.getContract(this.chaincodeName);
      this.initialized = true;
      console.log('FabricHelper: connected to Fabric network.');
    } catch (err) {
      console.error('Fabric init error — entering stub mode:', err.message);
      if (this.stubIfNoFabric) {
        this.stubMode = true;
        this.initialized = true;
      } else {
        throw err;
      }
    }
  }

  async submitTransaction(funcName, ...args) {
    await this.init();
    if (this.stubMode) return this._stubSubmit(funcName, args);
    try {
      const resultBuffer = await this.contract.submitTransaction(funcName, ...args);
      return resultBuffer.toString();
    } catch (err) {
      console.error('submitTransaction error:', err.message);
      if (this.stubIfNoFabric) return this._stubSubmit(funcName, args);
      throw err;
    }
  }

  async evaluateTransaction(funcName, ...args) {
    await this.init();
    if (this.stubMode) return this._stubEvaluate(funcName, args);
    try {
      const resultBuffer = await this.contract.evaluateTransaction(funcName, ...args);
      return resultBuffer.toString();
    } catch (err) {
      console.error('evaluateTransaction error:', err.message);
      if (this.stubIfNoFabric) return this._stubEvaluate(funcName, args);
      throw err;
    }
  }

  async _stubSubmit(funcName, args) {
    if (funcName === 'issueCertificate') {
      const [certId, issuer, studentName, course, issueDate, hash] = args;
      const tx = {
        txId: 'TX-' + uuidv4().split('-')[0].toUpperCase(),
        certId,
        issuer,
        studentName,
        course,
        issueDate,
        hash,
        status: 'issued',
        createdAt: new Date().toISOString()
      };
      return JSON.stringify(tx);
    }
    if (funcName === 'revokeCertificate') {
      const [certId, reason] = args;
      return JSON.stringify({ certId, status: 'revoked', reason, revokedAt: new Date().toISOString() });
    }
    return JSON.stringify({ message: `Stubbed submitTransaction called for ${funcName}`, args });
  }

  async _stubEvaluate(funcName, args) {
    if (funcName === 'queryCertificate') {
      const [certId] = args;
      const cert = {
        certId: certId,
        issuer: 'DemoInstitution',
        studentName: 'Demo Student',
        course: 'Demo Course',
        issueDate: new Date().toISOString().split('T')[0],
        hash: (args[1] || 'FAKEHASH-' + certId).toString(),
        status: 'issued',
        createdAt: new Date().toISOString()
      };
      return JSON.stringify(cert);
    }
    if (funcName === 'verifyCertificate') {
      const [certId, hashToCompare] = args;
      const storedHash = 'FAKEHASH-' + certId;
      const valid = (hashToCompare === storedHash) || Math.random() > 0.2;
      return JSON.stringify({ valid, cert: { certId, storedHash } });
    }
    return JSON.stringify({ message: `Stubbed evaluateTransaction for ${funcName}`, args });
  }

  async disconnect() {
    if (this.gateway) {
      try {
        await this.gateway.disconnect();
      } catch (e) {}
      this.gateway = null;
      this.contract = null;
      this.initialized = false;
    }
  }
}

module.exports = FabricHelper;