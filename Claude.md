# Bean Origin

## Project
Bean Origin is an AI-powered digital coffee commerce platform
connecting African coffee supply with global buyers.

## Stack
Backend:
- Laravel
- PHP
- PostgreSQL

Frontend:
- Vue
- Inertia.js
- Tailwind/Bootstrap where applicable

AI:
- AI agents
- LLM APIs
- RAG
- MCP
- structured tool calling

## Architecture Principles

1. Keep business logic separate from controllers.
2. Use Laravel Services for business operations.
3. Use Actions for discrete business operations where appropriate.
4. Use repositories only where they provide real value.
5. Keep AI agents separate from core business logic.
6. Agents must use tools/services to access business data.
7. Never allow an LLM to directly modify the database.
8. Validate all AI-generated database operations.
9. Use database transactions for financial/trading operations.
10. Maintain audit logs for important business actions.

## Core Business Domains

- Users
- Farms
- Farmers
- Cooperatives
- Coffee collections
- Coffee batches
- Coffee lots
- Inventory
- Quality
- Sustainability
- Traceability
- Buyers
- Sellers
- RFQs
- Offers
- Auctions
- Orders
- Payments
- Delivery
- Market intelligence
- AI agents

## Development Rules

Before modifying architecture:
1. Inspect existing implementation.
2. Identify dependencies.
3. Explain the proposed change.
4. Make the smallest appropriate change.
5. Test the change.
6. Check for regressions.

Do not create duplicate functionality.

Prefer existing Laravel functionality before introducing new packages.

Do not modify migrations that have already been used in production.

Never expose secrets, API keys or credentials.

When implementing a feature, consider:
- database
- backend
- API
- frontend
- validation
- authorization
- errors
- testing
- auditability

## AI Rules

AI is an intelligence layer, not the system of record.

The database remains the source of truth.

Agents should:
- reason
- retrieve information
- call approved tools
- perform validated actions
- explain important decisions

Agents should not:
- invent database facts
- bypass authorization
- directly execute unrestricted SQL
- modify critical business records without validation
