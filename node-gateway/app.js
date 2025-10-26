const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const FabricHelper = require('./fabric/fabricHelper');

const app = express();
const PORT = process.env.PORT || 4000;

app.use(cors());
app.use(bodyParser.json());

const fabric = new FabricHelper({
  connectionProfile: './fabric/connection.json',
  walletPath: './fabric/wallet',
  identity: process.env.FABRIC_IDENTITY || 'appUser',
  channelName: process.env.FABRIC_CHANNEL || 'mychannel',
  chaincodeName: process.env.FABRIC_CC || 'certificate-contract',
  stubIfNoFabric: true
});

app.post('/api/issue', async (req, res) => {
  try {
    const payload = req.body;
    const result = await fabric.submitTransaction('issueCertificate',
      payload.certId, payload.issuer, payload.studentName, payload.course, payload.issueDate, payload.hash
    );
    res.json({ success: true, result: JSON.parse(result) });
  } catch (err) {
    console.error('issue error', err);
    res.status(500).json({ success: false, message: err.message });
  }
});

app.post('/api/verify', async (req, res) => {
  try {
    const { certId, hash } = req.body;
    const result = await fabric.evaluateTransaction('verifyCertificate', certId, hash);
    res.json({ success: true, result: JSON.parse(result) });
  } catch (err) {
    console.error('verify error', err);
    res.status(500).json({ success: false, message: err.message });
  }
});

app.get('/api/query/:certId', async (req, res) => {
  try {
    const certId = req.params.certId;
    const result = await fabric.evaluateTransaction('queryCertificate', certId);
    res.json({ success: true, result: JSON.parse(result) });
  } catch (err) {
    console.error('query error', err);
    res.status(404).json({ success: false, message: err.message });
  }
});

app.get('/', (req, res) => {
  res.json({ message: 'E-Cert Fabric Gateway (stub-enabled) ✅' });
});

app.listen(PORT, () => {
  console.log(`Node gateway listening on http://localhost:${PORT}`);
});