#!/bin/bash

# Ollama
curl -fsSL https://ollama.com/install.sh | sh
ollama pull llama3:70b


# WREN AI
curl -L https://github.com/Canner/WrenAI/releases/latest/download/wren-launcher-linux.tar.gz | tar -xz && ./wren-launcher-linux

https://docs.getwren.ai/oss/ai_service/guide/custom_llm#running-wren-ai-with-your-custom-llm-or-document-store

wget -O .env.example https://raw.githubusercontent.com/canner/WrenAI/<WRENAI_VERSION_NUMBER>/docker/.env.example && \
mkdir -p ~/.wrenai && cp .env.example ~/.wrenai/.env

