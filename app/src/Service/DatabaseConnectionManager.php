<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Service to manage database connections and handle SQLite-specific optimizations
 * to prevent database locking issues
 */
class DatabaseConnectionManager
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Execute a callback with proper connection management and retry logic for SQLite
     */
    public function executeWithRetry(callable $callback, int $maxRetries = 3): mixed
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxRetries) {
            $attempt++;

            try {
                // Ensure connection is established and optimized
                $this->optimizeConnection();

                // Execute the callback
                $result = $callback();

                $this->logger->debug(sprintf('DatabaseConnectionManager: Operation completed successfully on attempt %d', $attempt));
                return $result;

            } catch (\Exception $e) {
                $lastException = $e;

                // Check if it's a SQLite locking error
                if ($this->isSqliteLockingError($e)) {
                    $this->logger->warning(sprintf('DatabaseConnectionManager: SQLite locking detected, retrying (attempt %d/%d): %s', $attempt, $maxRetries, $e->getMessage()));

                    if ($attempt < $maxRetries) {
                        // Close and reconnect to release any held locks
                        $this->closeConnection();

                        // Wait with exponential backoff
                        $waitTime = pow(2, $attempt - 1) * 100000; // 0.1s, 0.2s, 0.4s
                        usleep($waitTime);
                        continue;
                    }
                } else {
                    // Non-locking error, don't retry
                    $this->logger->error(sprintf('DatabaseConnectionManager: Non-recoverable database error: %s', $e->getMessage()));
                    throw $e;
                }
            }
        }

        // All retries exhausted
        $this->logger->error(sprintf('DatabaseConnectionManager: Database operation failed after %d attempts: %s', $maxRetries, $lastException->getMessage()));
        throw new \RuntimeException(sprintf('Database operation failed after %d attempts. Last error: %s', $maxRetries, $lastException->getMessage()), 0, $lastException);
    }

    /**
     * Optimize SQLite connection settings
     */
    private function optimizeConnection(): void
    {
        try {
            $connection = $this->entityManager->getConnection();

            if (!$connection->isConnected()) {
                $connection->connect();
            }

            // Apply SQLite optimizations if not already applied
            if (strpos($connection->getDatabasePlatform()->getName(), 'sqlite') !== false) {
                $this->applySqliteOptimizations($connection);
            }

        } catch (\Exception $e) {
            $this->logger->warning(sprintf('DatabaseConnectionManager: Failed to optimize connection: %s', $e->getMessage()));
        }
    }

    /**
     * Apply SQLite-specific optimizations
     */
    private function applySqliteOptimizations(Connection $connection): void
    {
        try {
            // These optimizations are applied at connection level
            // The doctrine.yaml configuration should handle most of these,
            // but we can apply additional optimizations here if needed

            $connection->executeStatement('PRAGMA temp_store=MEMORY');
            $connection->executeStatement('PRAGMA mmap_size=268435456'); // 256MB mmap

            $this->logger->debug('DatabaseConnectionManager: SQLite optimizations applied');

        } catch (\Exception $e) {
            $this->logger->warning(sprintf('DatabaseConnectionManager: Failed to apply SQLite optimizations: %s', $e->getMessage()));
        }
    }

    /**
     * Close the database connection to release locks
     */
    private function closeConnection(): void
    {
        try {
            $connection = $this->entityManager->getConnection();

            if ($connection->isConnected()) {
                // Clear any pending transactions
                if ($connection->isTransactionActive()) {
                    $connection->rollBack();
                }

                $connection->close();
                $this->logger->debug('DatabaseConnectionManager: Connection closed to release locks');
            }

        } catch (\Exception $e) {
            $this->logger->warning(sprintf('DatabaseConnectionManager: Failed to close connection: %s', $e->getMessage()));
        }
    }

    /**
     * Check if the exception is related to SQLite locking
     */
    private function isSqliteLockingError(\Exception $e): bool
    {
        $message = strtolower($e->getMessage());

        return strpos($message, 'database is locked') !== false
            || strpos($message, 'sqlite_busy') !== false
            || strpos($message, 'database file is locked') !== false
            || strpos($message, 'sql logic error') !== false;
    }

    /**
     * Get connection statistics for monitoring
     */
    public function getConnectionStats(): array
    {
        try {
            $connection = $this->entityManager->getConnection();

            return [
                'is_connected' => $connection->isConnected(),
                'is_transaction_active' => $connection->isTransactionActive(),
                'database_platform' => $connection->getDatabasePlatform()->getName(),
                'driver_name' => $connection->getDriver()->getName(),
            ];

        } catch (\Exception $e) {
            $this->logger->warning(sprintf('DatabaseConnectionManager: Failed to get connection stats: %s', $e->getMessage()));
            return ['error' => $e->getMessage()];
        }
    }
}
