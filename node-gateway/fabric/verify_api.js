// node-gateway/modules/verify_api.js
// Simple Express router stub for external verification API

const express = require('express');
const router = express.Router();

// GET /modules/verify/:certId
router.get('/verify/:certId', async (req, res) => {
  const certId = req.params.certId;
  // In production: query Fabric via FabricHelper
  // Here: return stubbed response or query your DB
  res.json({
    verified: true,
    cert: {
      certId,
      studentName: "Demo Student",
      course: "Demo Course",
      issuer: "Demo Institution",
      issueDate: "2025-10-25",
      txId: "TX-EXAMPLE"
    }
  });
});

module.exports = router;