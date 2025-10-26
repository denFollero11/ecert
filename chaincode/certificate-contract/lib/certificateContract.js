'use strict';

const { Contract } = require('fabric-contract-api');

class CertificateContract extends Contract {

    async initLedger(ctx) {
        // optional initial seeding
    }

    async issueCertificate(ctx, certId, issuer, studentName, course, issueDate, hash) {
        const exists = await this._exists(ctx, certId);
        if (exists) {
            throw new Error(`Certificate ${certId} already exists`);
        }
        const certificate = {
            certId,
            issuer,
            studentName,
            course,
            issueDate,
            hash,
            status: 'issued',
            createdAt: new Date().toISOString()
        };
        await ctx.stub.putState(certId, Buffer.from(JSON.stringify(certificate)));
        return JSON.stringify(certificate);
    }

    async queryCertificate(ctx, certId) {
        const data = await ctx.stub.getState(certId);
        if (!data || data.length === 0) {
            throw new Error(`Certificate ${certId} does not exist`);
        }
        return data.toString();
    }

    async verifyCertificate(ctx, certId, hashToCompare) {
        const data = await this.queryCertificate(ctx, certId);
        const cert = JSON.parse(data);
        return cert.hash === hashToCompare ? JSON.stringify({ valid: true, cert }) : JSON.stringify({ valid: false, cert });
    }

    async revokeCertificate(ctx, certId, reason) {
        const data = await this.queryCertificate(ctx, certId);
        const cert = JSON.parse(data);
        cert.status = 'revoked';
        cert.revokeReason = reason;
        cert.revokedAt = new Date().toISOString();
        await ctx.stub.putState(certId, Buffer.from(JSON.stringify(cert)));
        return JSON.stringify(cert);
    }

    async _exists(ctx, certId) {
        const data = await ctx.stub.getState(certId);
        return (data && data.length > 0);
    }
}

module.exports = CertificateContract;